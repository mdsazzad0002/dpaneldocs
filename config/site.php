<?php

/*
|--------------------------------------------------------------------------
| Public site settings
|--------------------------------------------------------------------------
|
| The documentation pages are static Markdown files in resources/docs. The
| "docs" list below is the sidebar: its order is the reading order, and a
| page only appears on the site (and in the sitemap) when it is listed here.
| Refresh the Markdown from a dPanel checkout with `php artisan docs:sync`.
|
*/

return [

    'name' => env('APP_NAME', 'dPanel'),

    'tagline' => 'Free, self-hosted web hosting control panel',

    'description' => 'dPanel is a free, self-hosted web hosting control panel built on Laravel, Vue, and Rust. Manage websites, databases, email, DNS, SSL, and backups on your own Linux server.',

    'support_email' => env('SUPPORT_EMAIL', 'dev@dengrweb.com'),

    'security_email' => env('SECURITY_EMAIL', 'dev@dengrweb.com'),

    'company' => [
        'name' => 'D Engr Web',
        'url' => 'https://dengrweb.com',
        'facebook' => 'https://www.facebook.com/dengrweblimited/',
    ],

    'repositories' => [
        'panel' => 'https://github.com/mdsazzad0002/dpanel',
        'docs' => 'https://github.com/mdsazzad0002/dpaneldocs',
    ],

    // Branch of the dPanel repository that "Sync docs" pulls from.
    'docs_branch' => env('DOCS_BRANCH', 'main'),

    'install_command' => "curl -fsSL https://raw.githubusercontent.com/mdsazzad0002/dpanel/main/installer.sh -o installer.sh\nchmod +x installer.sh\nsudo ./installer.sh",
    'development_command' => "git add .\ngit commit -m 'Update docs'\ngit pull origin main\ngit push origin main\ncd /var/www/dpanel && sudo npm run build \nsudo /var/www/drust/deploy/install-service.sh",

    'docs' => [
        'Getting started' => [
            'installation' => [
                'title' => 'Installation',
                'description' => 'Install dPanel on a fresh Linux server, choose a release, and set up website file permissions.',
            ],
            'architecture' => [
                'title' => 'Architecture',
                'description' => 'How the Laravel panel, the drust privileged API, and the Rust edge gateway fit together.',
            ],
            'operations' => [
                'title' => 'Operations',
                'description' => 'Everyday dPanel commands, rebuilds, updates, and troubleshooting steps.',
            ],
        ],
        'Reference' => [
            'dscript' => [
                'title' => 'dscript CLI',
                'description' => 'The dpanel command-line tool: chains, modules, scripts, dry runs, and recovery.',
            ],
            'drust-service' => [
                'title' => 'drust Service',
                'description' => 'Install, configure, and run the root-owned drust execution service.',
            ],
            'drust-api' => [
                'title' => 'drust API',
                'description' => 'Endpoint reference for the localhost-only drust execution API used by the panel.',
            ],
            'backups' => [
                'title' => 'Backups',
                'description' => 'Schedule dPanel backups, upload them to remote storage, and restore them.',
            ],
            'ssh-command-runner' => [
                'title' => 'Server Task Runner',
                'description' => 'SSH connector, command safety rules, and task reports in the Server Task Runner.',
            ],
            'whmcs' => [
                'title' => 'WHMCS Integration',
                'description' => 'Connect dPanel to WHMCS billing with signed API calls and single sign-on.',
            ],
        ],
        'Project' => [
            'contributing' => [
                'title' => 'Contributing',
                'description' => 'Clone dPanel, run the installer from your checkout, and open a pull request.',
            ],
            'security' => [
                'title' => 'Security Policy',
                'description' => 'How to report dPanel security issues privately and harden a public server.',
            ],
        ],
    ],

    /*
    | Development machines visitors can help buy. Shown as icons on the home
    | page; each one links to /donate/pc/{slug}. Prices are estimates in BDT.
    */
    'pcs' => [
        'ryzen-5-5600g' => [
            'name' => 'Starter build',
            'specs' => 'AMD Ryzen 5 5600G · 16 GB RAM · 512 GB NVMe',
            'price' => 45000,
            'use' => 'A quiet everyday machine for writing code and docs.',
        ],
        'intel-i5-12400' => [
            'name' => 'Office build',
            'specs' => 'Intel Core i5-12400 · 16 GB RAM · 512 GB NVMe',
            'price' => 55000,
            'use' => 'Runs the panel, the queue and a test site side by side.',
        ],
        'beelink-ser7' => [
            'name' => 'Mini PC',
            'specs' => 'Ryzen 7 7840HS mini PC · 32 GB RAM · 1 TB NVMe',
            'price' => 70000,
            'use' => 'A small always-on box for nightly installer tests.',
        ],
        'ryzen-5-7600' => [
            'name' => 'DDR5 build',
            'specs' => 'AMD Ryzen 5 7600 · 32 GB DDR5 · 1 TB NVMe',
            'price' => 75000,
            'use' => 'Fast Rust builds for drust and the edge gateway.',
        ],
        'intel-i5-13400' => [
            'name' => 'Balanced build',
            'specs' => 'Intel Core i5-13400 · 32 GB RAM · 1 TB NVMe',
            'price' => 80000,
            'use' => 'Several virtual servers for upgrade and migration tests.',
        ],
        'mac-mini-m4' => [
            'name' => 'Mac mini',
            'specs' => 'Apple Mac mini M4 · 16 GB RAM · 256 GB SSD',
            'price' => 85000,
            'use' => 'Checks the panel UI in Safari and on macOS.',
        ],
        'dell-poweredge-t150' => [
            'name' => 'Test server',
            'specs' => 'Dell PowerEdge T150 · Xeon E-2314 · 32 GB ECC · 2 TB',
            'price' => 90000,
            'use' => 'Real server hardware to test dPanel the way you run it.',
        ],
        'ryzen-7-7700' => [
            'name' => 'Developer build',
            'specs' => 'AMD Ryzen 7 7700 · 32 GB DDR5 · 1 TB NVMe',
            'price' => 95000,
            'use' => 'The main daily driver for building new features.',
        ],
        'intel-i7-13700' => [
            'name' => 'Power build',
            'specs' => 'Intel Core i7-13700 · 32 GB DDR5 · 2 TB NVMe',
            'price' => 110000,
            'use' => 'Full test suites and release builds in minutes.',
        ],
        'ryzen-9-7900' => [
            'name' => 'Workstation',
            'specs' => 'AMD Ryzen 9 7900 · 64 GB DDR5 · 2 TB NVMe',
            'price' => 140000,
            'use' => 'Many test servers at once across every supported Linux.',
        ],
    ],

    /*
    | Fixed donation accounts shown on every /donate/pc/{slug} page.
    */
    'donation_accounts' => [
        [
            'label' => 'Dutch-Bangla Bank',
            'type' => 'Bank transfer',
            'details' => [
                'Bank' => 'Dutch-Bangla Bank PLC',
                'Account name' => 'MD SAZZAD',
                'Account number' => '1641580479038',
                'Branch' => 'Mirpur 6, Dhaka, Bangladesh',
            ],
            'copy' => ['Account number'],
        ],
    ],

    'faq' => [
        [
            'q' => 'Is dPanel really free?',
            'a' => 'Yes. Every user gets the same software, the same features, and the same updates. There are no license fees, no feature locks, and no subscriptions. Paid help is available only if you ask for it.',
        ],
        [
            'q' => 'What server do I need?',
            'a' => 'A fresh Linux ubuntu server you control with sudo or root access, ports 80 and 443 open to the internet, and a domain name for the panel, for example panel.example.com.',
        ],
        [
            'q' => 'How do I update dPanel?',
            'a' => 'Run "sudo ./installer.sh update". The installer downloads the release straight from GitHub and records the installed version in the panel .env file.',
        ],
        [
            'q' => 'Can I migrate from cPanel or CyberPanel?',
            'a' => 'Yes. dPanel can import backups from cPanel and CyberPanel, and also restores its own portable backup packages.',
        ],
        [
            'q' => 'Can I sell hosting with dPanel?',
            'a' => 'Yes. You may run dPanel and sell hosting services with it. You may not redistribute, rebrand, or resell the software itself without written permission.',
        ],
        [
            'q' => 'How do I report a security problem?',
            'a' => 'Please do not open a public issue. Follow the Security Policy and report it privately by email.',
        ],
    ],

];
