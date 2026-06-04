// Jenin Care Performance Optimizations
// Advanced JavaScript performance improvements

class PerformanceOptimizer {
    constructor() {
        this.init();
    }

    init() {
        // Lazy loading for images
        this.setupLazyLoading();
        
        // Intersection Observer for animations
        this.setupIntersectionObserver();
        
        // Preload critical resources
        this.preloadCriticalResources();
        
        // Optimize event listeners
        this.optimizeEventListeners();
        
        // Setup service worker caching
        this.setupServiceWorkerCaching();
        
        // Minimize layout thrashing
        this.optimizeDOMManipulation();
    }

    setupLazyLoading() {
        // Native lazy loading support
        const images = document.querySelectorAll('img[data-src]');
        
        if ('loading' in HTMLImageElement.prototype) {
            // Use native lazy loading
            images.forEach(img => {
                img.src = img.dataset.src;
                img.loading = 'lazy';
            });
        } else {
            // Fallback to Intersection Observer
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => {
                img.classList.add('lazy');
                imageObserver.observe(img);
            });
        }
    }

    setupIntersectionObserver() {
        // Animate elements when they come into view
        const animatedElements = document.querySelectorAll('.animate-on-scroll');
        
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    element.classList.add('animate');
                    animationObserver.unobserve(element);
                }
            });
        }, { threshold: 0.1 });

        animatedElements.forEach(element => {
            animationObserver.observe(element);
        });
    }

    preloadCriticalResources() {
        const bp = window.basePath || '';
        const criticalResources = [
            bp + '/css/main.css',
            bp + '/js/app.js',
        ];

        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource;

            if (resource.endsWith('.css')) {
                link.as = 'style';
            } else if (resource.endsWith('.js')) {
                link.as = 'script';
            }

            document.head.appendChild(link);
        });
    }

    optimizeEventListeners() {
        // Debounce scroll events
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) {
                window.cancelAnimationFrame(scrollTimeout);
            }
            scrollTimeout = window.requestAnimationFrame(() => {
                this.handleScroll();
            });
        }, { passive: true });

        // Debounce resize events
        let resizeTimeout;
        window.addEventListener('resize', () => {
            if (resizeTimeout) {
                clearTimeout(resizeTimeout);
            }
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
    }

    handleScroll() {
        // Show/hide scroll to top button
        const scrollBtn = document.getElementById('scrollToTopV2');
        if (scrollBtn) {
            scrollBtn.style.opacity = window.scrollY > 400 ? '1' : '0';
            scrollBtn.style.pointerEvents = window.scrollY > 400 ? 'auto' : 'none';
        }
    }

    handleResize() {
        // Handle responsive adjustments
        this.updateResponsiveElements();
    }

    updateResponsiveElements() {
        // Update any responsive elements
        const isMobile = window.innerWidth < 768;
        
        document.body.classList.toggle('mobile', isMobile);
        document.body.classList.toggle('desktop', !isMobile);
    }

    setupServiceWorkerCaching() {
        // Skip service worker in development (SSL issues on localhost)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            return;
        }
        const bp = window.basePath || '';
        const importantPages = [bp + '/shop', bp + '/account', bp + '/cart'];

        importantPages.forEach(page => {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = page;
            document.head.appendChild(link);
        });
    }

    optimizeDOMManipulation() {
        // Batch DOM updates
        this.batchDOMUpdates();
    }

    batchDOMUpdates() {
        // Use requestAnimationFrame for smooth animations
        const elementsToUpdate = document.querySelectorAll('.update-on-scroll');
        
        const updateElements = () => {
            elementsToUpdate.forEach(element => {
                // Update elements in batch
                this.updateElement(element);
            });
        };

        requestAnimationFrame(updateElements);
    }

    updateElement(element) {
        // Update individual element
        if (element.dataset.update) {
            // Apply updates based on data attributes
            const updates = JSON.parse(element.dataset.update);
            Object.assign(element, updates);
        }
    }

    // Performance monitoring
    measurePerformance() {
        if ('performance' in window) {
            window.addEventListener('load', () => {
                const perfData = performance.getEntriesByType('navigation')[0];
                console.log('Page Load Time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
                console.log('DOM Interactive:', perfData.domInteractive - perfData.loadEventStart, 'ms');
                console.log('First Contentful Paint:', perfData.domContentLoadedEventStart - perfData.loadEventStart, 'ms');
            });
        }
    }

    // Optimize images dynamically
    optimizeImages() {
        const images = document.querySelectorAll('img');
        
        images.forEach(img => {
            // Add loading="lazy" if not present
            if (!img.hasAttribute('loading')) {
                img.loading = 'lazy';
            }
            
            // Add WebP support check
            if (this.supportsWebP()) {
                const webpSrc = img.src.replace(/\.(jpg|jpeg|png)$/, '.webp');
                this.testWebPSupport(webpSrc, img);
            }
        });
    }

    supportsWebP() {
        const canvas = document.createElement('canvas');
        return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }

    testWebPSupport(webpSrc, img) {
        const testImg = new Image();
        testImg.onload = () => {
            img.src = webpSrc;
        };
        testImg.onerror = () => {
            // Keep original image
        };
        testImg.src = webpSrc;
    }

    // Minimize reflows
    minimizeReflows() {
        // Use CSS transforms instead of changing layout properties
        const animatedElements = document.querySelectorAll('.animate-transform');
        
        animatedElements.forEach(element => {
            element.style.transform = 'translateZ(0)';
            element.style.willChange = 'transform';
        });
    }

    // Cleanup
    cleanup() {
        // Remove event listeners and clean up
        this.removeEventListeners();
        this.cleanupObservers();
    }

    removeEventListeners() {
        // Remove event listeners to prevent memory leaks
        // Implementation depends on your specific needs
    }

    cleanupObservers() {
        // Disconnect observers
        if (this.imageObserver) {
            this.imageObserver.disconnect();
        }
        if (this.animationObserver) {
            this.animationObserver.disconnect();
        }
    }
}

// Initialize performance optimizations
document.addEventListener('DOMContentLoaded', () => {
    const optimizer = new PerformanceOptimizer();
    
    // Monitor performance
    optimizer.measurePerformance();
    
    // Optimize images
    optimizer.optimizeImages();
    
    // Minimize reflows
    optimizer.minimizeReflows();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.performanceOptimizer) {
        window.performanceOptimizer.cleanup();
    }
});

// Export for global access
window.PerformanceOptimizer = PerformanceOptimizer;
