<?php

namespace App\Actions\Vendors;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Notifications\VendorApplicationReceived;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SubmitVendorApplication
{
    /**
     * Creates the vendor in `pending` state together with its documents and
     * payout account. Nothing is published until an admin approves it.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array{label: string, number: ?string, expires_at: ?string, file: ?UploadedFile}>  $documents
     */
    public function handle(User $user, array $data, array $documents = []): Vendor
    {
        $vendor = DB::transaction(function () use ($user, $data, $documents): Vendor {
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'legal_name' => $data['legal_name'],
                'about' => $data['about'] ?? null,
                'city' => $data['city'],
                'state' => $data['state'] ?? null,
                'country_code' => strtoupper($data['country_code']),
                'gst_number' => $data['gst_number'],
                'pan' => strtoupper($data['pan']),
                'iec_code' => $data['iec_code'],
                'cin' => $data['cin'] ?? null,
                'status' => Vendor::STATUS_PENDING,
                'payout_method' => 'bank_transfer',
                'meta' => [
                    'verticals' => $data['verticals'] ?? [],
                    'categories' => $data['categories'] ?? [],
                ],
            ]);

            $vendor->staff()->attach($user->id, ['role' => 'owner']);

            $this->storeStatutoryDocuments($vendor, $data);

            foreach ($documents as $document) {
                $this->storeDocument($vendor, $document);
            }

            $vendor->bankAccounts()->create([
                'account_holder' => $data['account_holder'],
                'account_no' => $data['account_no'],
                'ifsc' => $data['ifsc'] ?? null,
                'swift' => $data['swift'] ?? null,
                'bank_name' => $data['bank_name'],
                'branch' => $data['branch'] ?? null,
                'currency' => strtoupper($data['payout_currency']),
                'is_primary' => true,
            ]);

            $vendor->kycLogs()->create([
                'actor_id' => $user->id,
                'action' => 'application_submitted',
                'note' => 'Vendor application submitted for review.',
            ]);

            $user->update(['type' => User::TYPE_VENDOR]);
            $user->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

            return $vendor;
        });

        Notification::send($this->reviewers(), new VendorApplicationReceived($vendor));

        return $vendor;
    }

    /** GST/PAN/IEC are captured as documents too so the KYC queue is one list. */
    private function storeStatutoryDocuments(Vendor $vendor, array $data): void
    {
        $statutory = [
            'gst' => ['GST', $data['gst_number'] ?? null],
            'pan' => ['PAN', $data['pan'] ?? null],
            'iec' => ['IEC', $data['iec_code'] ?? null],
            'cin' => ['CIN', $data['cin'] ?? null],
        ];

        foreach ($statutory as $type => [$label, $number]) {
            if (! $number) {
                continue;
            }

            $vendor->documents()->create([
                'type' => $type,
                'label' => $label,
                'number' => $number,
                'status' => VendorDocument::STATUS_PENDING,
                'is_public' => false,
            ]);
        }
    }

    private function storeDocument(Vendor $vendor, array $document): void
    {
        if (empty($document['label'])) {
            return;
        }

        $path = null;

        if (($document['file'] ?? null) instanceof UploadedFile) {
            // Private disk — KYC paperwork must never be publicly reachable.
            $path = $document['file']->store("vendor-documents/{$vendor->id}", 'local');
        }

        $vendor->documents()->create([
            'type' => Str::slug($document['label'], '_'),
            'label' => $document['label'],
            'number' => $document['number'] ?? null,
            'expires_at' => $document['expires_at'] ?? null,
            'file_path' => $path,
            'status' => VendorDocument::STATUS_PENDING,
            'is_public' => true,
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Vendor::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function reviewers()
    {
        return User::where('type', User::TYPE_ADMIN)->get();
    }
}
