/**
 * Thin fetch wrapper for the JSON endpoints under /x/*.
 * Keeps CSRF handling and error shape in one place.
 */
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function request(method, url, body = null) {
    const response = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
        },
        credentials: 'same-origin',
        body: body ? JSON.stringify(body) : null,
    });

    const payload = response.status === 204 ? null : await response.json().catch(() => null);

    if (! response.ok) {
        throw Object.assign(new Error(payload?.message ?? 'Request failed'), {
            status: response.status,
            payload,
        });
    }

    return payload;
}

export const http = {
    get: (url) => request('GET', url),
    post: (url, body) => request('POST', url, body),
    patch: (url, body) => request('PATCH', url, body),
    delete: (url) => request('DELETE', url),
};

export default http;
