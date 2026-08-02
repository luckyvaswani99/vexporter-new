/**
 * Reveals `.section-reveal` elements as they enter the viewport.
 * Mirrors the IntersectionObserver behaviour from the approved design.
 */
export default function initScrollReveal() {
    const elements = document.querySelectorAll('.section-reveal');

    if (! elements.length) {
        return;
    }

    if (! ('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        elements.forEach((element) => element.classList.add('visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -50px 0px' },
    );

    elements.forEach((element) => observer.observe(element));
}
