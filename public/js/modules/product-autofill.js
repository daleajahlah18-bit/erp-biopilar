class ProductAutoFill {
    constructor(options = {}) {
        this.containerSelector = options.container || 'body';
        this.cache = new Map();
        
        this.init();
    }

    init() {
        const container = document.querySelector(this.containerSelector);
        if (!container) return;

        // Listen for native change events (in case Select2 is not used)
        container.addEventListener('change', (e) => {
            if (e.target && e.target.classList && e.target.classList.contains('product-select')) {
                this.handleProductChange(e.target);
            }
        });

        // jQuery Select2 support (Select2 triggers jQuery events, not native DOM events that bubble perfectly)
        if (typeof window.jQuery !== 'undefined') {
            window.jQuery(this.containerSelector).on('change', '.product-select', (e) => {
                this.handleProductChange(e.target);
            });
        }
    }

    async handleProductChange(selectElement) {
        const productId = selectElement.value;
        const row = selectElement.closest('tr') || selectElement.closest('.form-row');
        if (!row || !productId) return;

        const unitSelect = row.querySelector('.unit-select');
        const priceInput = row.querySelector('.input-price');

        // Show loading state
        this.setLoadingState(unitSelect, true);
        this.setLoadingState(priceInput, true);

        try {
            let data;
            if (this.cache.has(productId)) {
                data = this.cache.get(productId);
            } else {
                const response = await fetch(`/api/products/${productId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Network response was not ok');
                data = await response.json();
                this.cache.set(productId, data);
            }

            // Auto-fill Unit
            if (unitSelect && data.unit_id) {
                // If it's a select2, we need to trigger change
                window.jQuery(unitSelect).val(data.unit_id).trigger('change');
            }

            // Auto-fill Price if available
            if (priceInput && data.last_purchase_price) {
                priceInput.value = parseInt(data.last_purchase_price, 10);
                
                // Trigger change to recalculate totals
                priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                priceInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

        } catch (error) {
            console.error('Failed to fetch product details:', error);
            // Optional: show a small toast or inline error
        } finally {
            this.setLoadingState(unitSelect, false);
            this.setLoadingState(priceInput, false);
        }
    }

    setLoadingState(element, isLoading) {
        if (!element) return;
        
        if (isLoading) {
            // Find or create loading indicator
            let loader = element.parentNode.querySelector('.autofill-loader');
            if (!loader) {
                loader = document.createElement('div');
                loader.className = 'spinner-border spinner-border-sm text-primary autofill-loader';
                loader.setAttribute('role', 'status');
                loader.style.position = 'absolute';
                loader.style.right = '35px';
                loader.style.top = '12px';
                loader.style.zIndex = '10';
                
                // Make parent relative if it isn't
                if (window.getComputedStyle(element.parentNode).position === 'static') {
                    element.parentNode.style.position = 'relative';
                }
                element.parentNode.appendChild(loader);
            }
            loader.style.display = 'block';
            element.style.opacity = '0.5';
        } else {
            const loader = element.parentNode.querySelector('.autofill-loader');
            if (loader) loader.style.display = 'none';
            element.style.opacity = '1';
        }
    }
}
