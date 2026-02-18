(function () {
    if (window.__ADMIN_SHELL_INITIALIZED__) {
        return;
    }
    window.__ADMIN_SHELL_INITIALIZED__ = true;

    var currentPath = (window.location.pathname || '').toLowerCase();
    if (currentPath.indexOf('/admin/') === -1 || currentPath.indexOf('/admin/login.php') !== -1) {
        return;
    }

    var shouldEnableDesktopShell = window.matchMedia('(min-width: 992px)').matches;
    if (!shouldEnableDesktopShell) {
        return;
    }

    function detectActiveRoute() {
        var routeMap = {
            index: ['/admin/index.php', '/admin/groups.php'],
            order: ['/admin/order.php'],
            review: ['/admin/review_list.php', '/admin/review_details.php'],
            visitor: ['/admin/visitor.php'],
            settings: ['/admin/settings.php'],
            settings_template: ['/admin/moban.php', '/admin/settings_page.php', '/admin/settings_page2.php'],
            settings_task: ['/admin/settings_frontend.php'],
            settings_account: ['/admin/admin_settings.php'],
            settings_payment: ['/admin/payment_settings.php'],
            settings_audit: ['/admin/audit_status.php'],
            settings_brand: ['/admin/brand_settings.php'],
            other: ['/admin/upload_cache.php']
        };

        var active = '';
        Object.keys(routeMap).some(function (key) {
            return routeMap[key].some(function (path) {
                if (currentPath.indexOf(path) !== -1) {
                    active = key;
                    return true;
                }
                return false;
            });
        });
        return active;
    }

    function ensureFavicon(path) {
        if (!path) {
            return;
        }
        var link = document.querySelector('link[rel="icon"]');
        if (!link) {
            link = document.createElement('link');
            link.rel = 'icon';
            document.head.appendChild(link);
        }
        link.href = path + (path.indexOf('?') === -1 ? '?v=' : '&v=') + Date.now();
    }

    function getBadgeText(name) {
        var plain = (name || '后台').replace(/\s+/g, '');
        return plain.slice(0, 2);
    }

    function buildShell(brand) {
        if (document.querySelector('.admin-shell')) {
            return;
        }

        var activeRoute = detectActiveRoute();
        var brandName = (brand && brand.brand_name) ? brand.brand_name : '付费进群系统';
        var logoPath = brand && brand.logo_path ? brand.logo_path : '';

        var shell = document.createElement('div');
        shell.className = 'admin-shell';

        var aside = document.createElement('aside');
        aside.className = 'admin-shell-sidebar';

        var brandWrap = document.createElement('a');
        brandWrap.className = 'admin-shell-brand';
        brandWrap.href = 'index.php';

        if (logoPath) {
            var logo = document.createElement('img');
            logo.className = 'admin-shell-brand-logo';
            logo.src = logoPath;
            logo.alt = brandName;
            brandWrap.appendChild(logo);
        } else {
            var badge = document.createElement('span');
            badge.className = 'admin-shell-brand-badge';
            badge.textContent = getBadgeText(brandName);
            brandWrap.appendChild(badge);
        }

        var brandText = document.createElement('span');
        brandText.className = 'admin-shell-brand-text';
        brandText.textContent = brandName;
        brandWrap.appendChild(brandText);
        aside.appendChild(brandWrap);

        var sectionTitle = document.createElement('p');
        sectionTitle.className = 'admin-shell-title';
        sectionTitle.textContent = '功能菜单';
        aside.appendChild(sectionTitle);

        var nav = document.createElement('nav');
        nav.className = 'admin-shell-nav';
        var menus = [
            { key: 'index', label: '控制台', href: 'index.php' },
            { key: 'order', label: '查看订单', href: 'order.php' },
            { key: 'review', label: '任务审核', href: 'review_list.php' },
            { key: 'visitor', label: '访客记录', href: 'visitor.php' },
            {
                key: 'settings',
                label: '系统设置',
                href: 'settings.php',
                children: [
                    { key: 'settings_template', label: '模板设置', href: 'moban.php' },
                    { key: 'settings_task', label: '任务设置', href: 'settings_frontend.php' },
                    { key: 'settings_account', label: '账号设置', href: 'admin_settings.php' },
                    { key: 'settings_payment', label: '支付设置', href: 'payment_settings.php' },
                    { key: 'settings_audit', label: '审核设置', href: 'audit_status.php' },
                    { key: 'settings_brand', label: '品牌设置', href: 'brand_settings.php' }
                ]
            },
            { key: 'other', label: '其他工具', href: 'upload_cache.php' }
        ];

        menus.forEach(function (menu) {
            var link = document.createElement('a');
            link.className = 'admin-shell-link';
            if (menu.key === activeRoute || (menu.key === 'settings' && String(activeRoute).indexOf('settings_') === 0)) {
                link.classList.add('is-active');
            }
            link.href = menu.href;
            link.textContent = menu.label;
            nav.appendChild(link);

            if (menu.children && menu.children.length) {
                var sub = document.createElement('div');
                sub.className = 'admin-shell-subnav';
                if (menu.key === activeRoute || String(activeRoute).indexOf('settings_') === 0) {
                    sub.classList.add('is-open');
                }

                menu.children.forEach(function (child) {
                    var childLink = document.createElement('a');
                    childLink.className = 'admin-shell-sublink';
                    if (child.key === activeRoute) {
                        childLink.classList.add('is-active');
                    }
                    childLink.href = child.href;
                    childLink.textContent = child.label;
                    sub.appendChild(childLink);
                });

                nav.appendChild(sub);
            }
        });
        aside.appendChild(nav);

        var logoutForm = document.createElement('form');
        logoutForm.action = 'logout.php';
        logoutForm.method = 'post';
        logoutForm.className = 'admin-shell-logout-form';
        logoutForm.innerHTML = '<button class="admin-shell-logout" type="submit">退出登录</button>';
        aside.appendChild(logoutForm);

        var content = document.createElement('div');
        content.className = 'admin-shell-content';

        var nodes = Array.prototype.slice.call(document.body.childNodes);
        nodes.forEach(function (node) {
            if (node === shell) {
                return;
            }
            content.appendChild(node);
        });

        shell.appendChild(aside);
        shell.appendChild(content);

        document.body.appendChild(shell);
        document.body.classList.add('admin-shell-enabled');
    }

    function initShell() {
        fetch('brand_settings_api.php', { credentials: 'same-origin' })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                var data = payload && payload.success ? payload.data : {};
                ensureFavicon(data && data.favicon_path ? data.favicon_path : '');
                buildShell(data || {});
            })
            .catch(function () {
                buildShell({});
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initShell);
    } else {
        initShell();
    }
})();
