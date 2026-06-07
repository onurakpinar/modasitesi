const DESKTOP_MEDIA = '(min-width: 1024px)';

const isDesktopViewport = () => window.matchMedia(DESKTOP_MEDIA).matches;

export function registerNavigationComponents(Alpine) {
    Alpine.data('siteHeader', () => ({
        mobileMenuOpen: false,
        menuButtonShowsOpen: true,
        categoriesOpen: false,
        categoriesChevronClass: '',
        mobileCategoriesOpen: false,
        mobileCategoriesChevronClass: '',
        _resizeHandler: null,

        init() {
            this.closeMobileMenu(false);
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
                this.closeMobileMenu(false);
            }
        },

        setHeaderOffset() {
            const bar = this.$refs.headerBar;

            if (!bar) {
                return;
            }

            document.documentElement.style.setProperty('--site-header-offset', `${bar.offsetHeight}px`);
        },

        toggleMobileMenu() {
            if (this.mobileMenuOpen) {
                this.closeMobileMenu();

                return;
            }

            this.mobileMenuOpen = true;
            this.menuButtonShowsOpen = false;
            this.categoriesOpen = false;
            this.mobileCategoriesOpen = false;
            this.mobileCategoriesChevronClass = '';
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.mobileNav?.querySelector('a, button')?.focus());
        },

        closeMobileMenu(focusButton = true) {
            this.mobileMenuOpen = false;
            this.menuButtonShowsOpen = true;
            this.mobileCategoriesOpen = false;
            this.mobileCategoriesChevronClass = '';
            this.categoriesOpen = false;
            this.categoriesChevronClass = '';
            document.body.classList.remove('overflow-hidden');

            if (focusButton) {
                this.$nextTick(() => this.$refs.menuButton?.focus());
            }
        },

        toggleCategories() {
            this.categoriesOpen = !this.categoriesOpen;
            this.categoriesChevronClass = this.categoriesOpen ? 'rotate-180' : '';
        },

        closeCategories() {
            this.categoriesOpen = false;
            this.categoriesChevronClass = '';
        },

        toggleMobileCategories() {
            this.mobileCategoriesOpen = !this.mobileCategoriesOpen;
            this.mobileCategoriesChevronClass = this.mobileCategoriesOpen ? 'rotate-180' : '';
        },
    }));

    Alpine.data('footerSection', () => ({
        open: false,
        chevronClass: '',
        panelClass: 'hidden md:block',

        toggle() {
            this.open = !this.open;
            this.chevronClass = this.open ? 'rotate-180' : '';
            this.panelClass = this.open ? 'block' : 'hidden md:block';
        },
    }));

    Alpine.data('adminShell', () => ({
        sidebarOpen: false,
        menuButtonShowsOpen: true,
        sidebarTranslateClass: '',
        sidebarAriaHidden: true,
        _resizeHandler: null,

        init() {
            this.syncSidebarState();
            this._resizeHandler = () => {
                if (isDesktopViewport()) {
                    this.closeSidebar(false);
                }

                this.syncSidebarState();
            };

            window.addEventListener('resize', this._resizeHandler);
        },

        syncSidebarState() {
            this.sidebarTranslateClass = this.sidebarOpen ? 'translate-x-0' : '';
            this.sidebarAriaHidden = !isDesktopViewport() && !this.sidebarOpen;
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
            this.menuButtonShowsOpen = false;
            this.syncSidebarState();
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.adminSidebar?.querySelector('a')?.focus());
        },

        closeSidebar(focusMenu = true) {
            if (!this.sidebarOpen) {
                return;
            }

            this.sidebarOpen = false;
            this.menuButtonShowsOpen = true;
            this.syncSidebarState();
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
}
