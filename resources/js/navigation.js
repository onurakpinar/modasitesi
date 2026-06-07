const DESKTOP_MEDIA = '(min-width: 1024px)';

const isDesktopViewport = () => window.matchMedia(DESKTOP_MEDIA).matches;

document.addEventListener('alpine:init', () => {
    Alpine.data('siteHeader', () => ({
        open: false,
        categoriesOpen: false,
        mobileAccordion: null,
        _resizeHandler: null,

        init() {
            this.syncViewport();
            this._resizeHandler = () => this.syncViewport();
            window.addEventListener('resize', this._resizeHandler);
        },

        destroy() {
            if (this._resizeHandler) {
                window.removeEventListener('resize', this._resizeHandler);
            }

            document.body.classList.remove('overflow-hidden');
        },

        syncViewport() {
            this.setHeaderOffset();

            if (isDesktopViewport()) {
                this.resetMobileState();
            }
        },

        setHeaderOffset() {
            const bar = this.$refs.headerBar;

            if (!bar) {
                return;
            }

            document.documentElement.style.setProperty('--site-header-offset', `${bar.offsetHeight}px`);
        },

        resetMobileState() {
            this.open = false;
            this.mobileAccordion = null;
            document.body.classList.remove('overflow-hidden');
        },

        toggle() {
            if (this.open) {
                this.close();

                return;
            }

            this.open = true;
            this.categoriesOpen = false;
            this.mobileAccordion = null;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.mobileNav?.querySelector('a, button')?.focus());
        },

        close() {
            if (!this.open) {
                return;
            }

            this.open = false;
            this.mobileAccordion = null;
            document.body.classList.remove('overflow-hidden');
            this.$nextTick(() => this.$refs.menuButton?.focus());
        },

        toggleCategories() {
            this.categoriesOpen = !this.categoriesOpen;
        },

        closeCategories() {
            this.categoriesOpen = false;
        },

        toggleMobileAccordion(id) {
            this.mobileAccordion = this.mobileAccordion === id ? null : id;
        },

        isMobileAccordionOpen(id) {
            return this.mobileAccordion === id;
        },
    }));

    Alpine.data('footerAccordion', () => ({
        openSection: null,

        toggleSection(id) {
            this.openSection = this.openSection === id ? null : id;
        },

        isSectionOpen(id) {
            return this.openSection === id;
        },

        sectionPanelClass(id) {
            return this.isSectionOpen(id) ? 'block' : 'hidden md:block';
        },
    }));

    Alpine.data('adminShell', () => ({
        sidebarOpen: false,
        _resizeHandler: null,

        init() {
            this._resizeHandler = () => {
                if (isDesktopViewport()) {
                    this.closeSidebar(false);
                }
            };

            window.addEventListener('resize', this._resizeHandler);
        },

        destroy() {
            if (this._resizeHandler) {
                window.removeEventListener('resize', this._resizeHandler);
            }

            document.body.classList.remove('overflow-hidden');
        },

        isDesktop() {
            return isDesktopViewport();
        },

        toggleSidebar() {
            if (this.sidebarOpen) {
                this.closeSidebar();

                return;
            }

            this.sidebarOpen = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.adminSidebar?.querySelector('a')?.focus());
        },

        closeSidebar(focusMenu = true) {
            if (!this.sidebarOpen) {
                return;
            }

            this.sidebarOpen = false;
            document.body.classList.remove('overflow-hidden');

            if (focusMenu) {
                this.$nextTick(() => this.$refs.adminMenuButton?.focus());
            }
        },

        closeSidebarOnNavigate() {
            if (!this.isDesktop()) {
                this.closeSidebar(false);
            }
        },
    }));
});
