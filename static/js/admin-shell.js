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

    var HEAD_MANAGED_ATTR = 'data-admin-shell-managed';

    function clearManagedHeadResources() {
        Array.prototype.forEach.call(document.head.querySelectorAll('[' + HEAD_MANAGED_ATTR + '="1"]'), function (node) {
            if (node && node.parentNode) {
                node.parentNode.removeChild(node);
            }
        });
    }

    function syncHeadResourcesFromDoc(sourceDoc, baseUrl) {
        if (!sourceDoc || !sourceDoc.head) {
            return;
        }

        if (sourceDoc === document) {
            Array.prototype.forEach.call(document.head.children, function (node) {
                var tag = (node.tagName || '').toLowerCase();
                if (tag === 'style') {
                    node.setAttribute(HEAD_MANAGED_ATTR, '1');
                    return;
                }
                if (tag === 'link') {
                    var rel = String(node.getAttribute('rel') || '').toLowerCase();
                    if (rel.indexOf('stylesheet') !== -1) {
                        node.setAttribute(HEAD_MANAGED_ATTR, '1');
                    }
                }
            });
            return;
        }

        clearManagedHeadResources();

        Array.prototype.forEach.call(sourceDoc.head.children, function (node) {
            var tag = (node.tagName || '').toLowerCase();
            if (tag !== 'style' && tag !== 'link') {
                return;
            }

            if (tag === 'link') {
                var rel = String(node.getAttribute('rel') || '').toLowerCase();
                if (rel.indexOf('stylesheet') === -1) {
                    return;
                }
            }

            var clone = node.cloneNode(true);
            clone.setAttribute(HEAD_MANAGED_ATTR, '1');

            if (tag === 'link') {
                var href = node.getAttribute('href');
                if (href) {
                    clone.setAttribute('href', new URL(href, baseUrl || window.location.href).toString());
                }
            }

            document.head.appendChild(clone);
        });
    }

    function syncBodyClassFromDoc(sourceDoc) {
        if (!sourceDoc || !sourceDoc.body) {
            return;
        }

        var preserved = [];
        if (document.body.classList.contains('admin-shell-enabled')) {
            preserved.push('admin-shell-enabled');
        }
        if (document.body.classList.contains('admin-shell-routing')) {
            preserved.push('admin-shell-routing');
        }

        var incoming = String(sourceDoc.body.className || '')
            .split(/\s+/)
            .filter(Boolean);

        var merged = preserved.slice();
        incoming.forEach(function (cls) {
            if (merged.indexOf(cls) === -1) {
                merged.push(cls);
            }
        });
        document.body.className = merged.join(' ');
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

    function refreshActiveState() {
        var activeRoute = detectActiveRoute();
        var isSettingsActive = activeRoute === 'settings' || String(activeRoute).indexOf('settings_') === 0;

        Array.prototype.forEach.call(document.querySelectorAll('.admin-shell-link'), function (link) {
            var key = link.getAttribute('data-route-key') || '';
            if (key && (key === activeRoute || (key === 'settings' && isSettingsActive))) {
                link.classList.add('is-active');
            } else {
                link.classList.remove('is-active');
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('.admin-shell-sublink'), function (link) {
            var key = link.getAttribute('data-route-key') || '';
            if (key && key === activeRoute) {
                link.classList.add('is-active');
            } else {
                link.classList.remove('is-active');
            }
        });
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

    var shellNavigationState = {
        isRouting: false,
        controller: null
    };

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
            link.setAttribute('data-route-key', menu.key);
            if (menu.key === activeRoute || (menu.key === 'settings' && String(activeRoute).indexOf('settings_') === 0)) {
                link.classList.add('is-active');
            }
            link.href = menu.href;
            link.textContent = menu.label;
            nav.appendChild(link);

            if (menu.children && menu.children.length) {
                var sub = document.createElement('div');
                sub.className = 'admin-shell-subnav is-open';

                menu.children.forEach(function (child) {
                    var childLink = document.createElement('a');
                    childLink.className = 'admin-shell-sublink';
                    childLink.setAttribute('data-route-key', child.key);
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

    function executeScripts(container) {
        var scripts = container.querySelectorAll('script');
        Array.prototype.forEach.call(scripts, function (oldScript) {
            var src = oldScript.getAttribute('src') || '';
            if (src.indexOf('admin-shell.js') !== -1) {
                oldScript.parentNode.removeChild(oldScript);
                return;
            }

            var newScript = document.createElement('script');
            Array.prototype.forEach.call(oldScript.attributes, function (attr) {
                newScript.setAttribute(attr.name, attr.value);
            });
            if (!src) {
                newScript.textContent = oldScript.textContent;
            }
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    function renderFetchedPage(htmlText, targetUrl) {
        var parser = new DOMParser();
        var parsedDoc = parser.parseFromString(htmlText, 'text/html');
        var parsedPath = (new URL(targetUrl, window.location.origin)).pathname.toLowerCase();

        if (parsedPath.indexOf('/admin/login.php') !== -1) {
            window.location.href = targetUrl;
            return false;
        }

        var content = document.querySelector('.admin-shell-content');
        if (!content || !parsedDoc.body) {
            window.location.href = targetUrl;
            return false;
        }

        var wrapper = document.createElement('div');
        wrapper.innerHTML = parsedDoc.body.innerHTML;

        Array.prototype.forEach.call(wrapper.querySelectorAll('script[src*="admin-shell.js"]'), function (script) {
            script.parentNode.removeChild(script);
        });

        syncHeadResourcesFromDoc(parsedDoc, targetUrl);
        syncBodyClassFromDoc(parsedDoc);
        content.innerHTML = wrapper.innerHTML;
        executeScripts(content);

        if (parsedDoc.title) {
            document.title = parsedDoc.title;
        }

        currentPath = parsedPath;
        refreshActiveState();
        window.scrollTo(0, 0);
        window.dispatchEvent(new CustomEvent('admin-shell:page-loaded', {
            detail: { url: targetUrl }
        }));
        return true;
    }

    function navigateWithShell(targetUrl, options) {
        var config = options || {};
        if (shellNavigationState.isRouting) {
            if (shellNavigationState.controller && typeof shellNavigationState.controller.abort === 'function') {
                shellNavigationState.controller.abort();
            } else {
                return;
            }
        }

        var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        shellNavigationState.controller = controller;
        shellNavigationState.isRouting = true;
        document.body.classList.add('admin-shell-routing');

        fetch(targetUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'AdminShell'
            },
            signal: controller ? controller.signal : undefined
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('请求失败');
                }
                return Promise.all([response.text(), response.url || targetUrl]);
            })
            .then(function (payload) {
                var htmlText = payload[0];
                var finalUrl = payload[1];
                var success = renderFetchedPage(htmlText, finalUrl);
                if (!success || config.fromPopState) {
                    return;
                }

                if (config.replaceState) {
                    window.history.replaceState({ shell: true }, '', finalUrl);
                } else {
                    window.history.pushState({ shell: true }, '', finalUrl);
                }
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                window.location.href = targetUrl;
            })
            .finally(function () {
                shellNavigationState.isRouting = false;
                shellNavigationState.controller = null;
                document.body.classList.remove('admin-shell-routing');
            });
    }

    function bindShellNavigation() {
        document.addEventListener('click', function (event) {
            var link = event.target.closest('.admin-shell-link, .admin-shell-sublink, .admin-shell-brand');
            if (!link) {
                return;
            }
            if (!link.closest('.admin-shell-sidebar')) {
                return;
            }

            var href = link.getAttribute('href') || '';
            if (!href || href.indexOf('logout.php') !== -1 || href.indexOf('javascript:') === 0 || href.indexOf('#') === 0) {
                return;
            }

            if (event.button !== 0) {
                return;
            }

            if (event.ctrlKey || event.shiftKey || event.metaKey || event.altKey) {
                return;
            }

            event.preventDefault();
            var targetUrl = new URL(href, window.location.href).toString();
            if (targetUrl === window.location.href) {
                return;
            }
            navigateWithShell(targetUrl, { replaceState: false });
        });

        window.addEventListener('popstate', function () {
            navigateWithShell(window.location.href, { fromPopState: true });
        });
    }

    function initShell() {
        syncHeadResourcesFromDoc(document, window.location.href);

        fetch('brand_settings_api.php', { credentials: 'same-origin' })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                var data = payload && payload.success ? payload.data : {};
                ensureFavicon(data && data.favicon_path ? data.favicon_path : '');
                buildShell(data || {});
                refreshActiveState();
                window.history.replaceState({ shell: true }, '', window.location.href);
                bindShellNavigation();
            })
            .catch(function () {
                buildShell({});
                refreshActiveState();
                window.history.replaceState({ shell: true }, '', window.location.href);
                bindShellNavigation();
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initShell);
    } else {
        initShell();
    }
})();
