<?php declare(strict_types=1);
namespace hiperesp\server\controllers\web;

use hiperesp\server\attributes\Inject;
use hiperesp\server\attributes\Request;
use hiperesp\server\controllers\Controller;
use hiperesp\server\enums\Input;
use hiperesp\server\enums\Output;
use hiperesp\server\services\AdminService;

class AdminController extends Controller {

    #[Inject] private AdminService $adminService;

    // ── Auth helpers ──────────────────────────────────────────────────────────

    private function startSession(): void {
        if (\session_status() === \PHP_SESSION_NONE) {
            \session_start();
        }
    }

    private function isLoggedIn(): bool {
        $this->startSession();
        return !empty($_SESSION['admin_authed']);
    }

    private function requireAuth(): ?string {
        if (!$this->isLoggedIn()) {
            return $this->loginPage('');
        }
        return null;
    }

    private function adminPassword(): string {
        global $config;
        return $config['ADMIN_PASSWORD'] ?? 'admin';
    }

    // ── Layout ────────────────────────────────────────────────────────────────

    private function layout(string $title, string $body): string {
        $logoutForm = $this->isLoggedIn()
            ? '<form method="post" action="logout" style="display:inline"><button class="btn-danger">Logout</button></form>'
            : '';
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>{$title} — DFPS Admin</title>
        <style>
            *{box-sizing:border-box;margin:0;padding:0}
            body{font-family:'Inter',system-ui,-apple-system,sans-serif;background:#0d0905;color:#e2d1a3;min-height:100vh;line-height:1.5}
            a{color:#ffb347;text-decoration:none;transition:color 0.2s}a:hover{color:#ffd28e;text-decoration:none}
            header{background:linear-gradient(180deg, #1c140a 0%, #150f07 100%);border-bottom:2px solid #4a3215;padding:14px 28px;display:flex;align-items:center;gap:20px;box-shadow:0 4px 12px rgba(0,0,0,0.5)}
            header h1{font-size:1.3rem;color:#ffb347;flex:1;font-weight:700;letter-spacing:-0.01em}
            header a{font-size:0.95rem;font-weight:500;color:#c0a080}header a:hover{color:#ffb347}
            .container{max-width:1080px;margin:32px auto;padding:0 24px}
            .card{background:#181109;border:1px solid #362818;border-radius:12px;padding:24px;margin-bottom:24px;box-shadow:0 8px 24px rgba(0,0,0,0.3)}
            .card h2{color:#ffb347;margin-bottom:20px;font-size:1.2rem;border-bottom:1px solid #362818;padding-bottom:12px;font-weight:600}
            table{width:100%;border-collapse:separate;border-spacing:0}
            th,td{padding:12px 16px;text-align:left;border-bottom:1px solid #2a1f12}
            th{color:#8a7050;font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;font-weight:700}
            tr:hover td{background:#20170c}
            tr:last-child td{border-bottom:none}
            input[type=text],input[type=number],input[type=password],input[type=email],textarea,select{background:#0a0704;border:1px solid #4a3825;color:#e2d1a3;padding:8px 12px;border-radius:6px;width:100%;transition:border-color 0.2s, box-shadow 0.2s;font-size:0.95rem}
            input:focus,textarea:focus,select:focus{outline:none;border-color:#ffb347;box-shadow:0 0 0 2px rgba(255,179,71,0.1)}
            textarea{resize:vertical;min-height:80px}
            .btn{background:#4a3215;color:#e2d1a3;border:1px solid #634621;padding:8px 18px;border-radius:6px;cursor:pointer;font-size:.9rem;font-weight:600;transition:all 0.2s;display:inline-flex;align-items:center;justify-content:center;gap:6px}
            .btn:hover{background:#5c401c;border-color:#7a5a2d;transform:translateY(-1px)}
            .btn:active{transform:translateY(0)}
            .btn-danger{background:#5a1818;color:#ffd0d0;border:1px solid #7a2525}
            .btn-danger:hover{background:#7a2525;border-color:#9a3535}
            .btn-success{background:#1a3d1a;color:#d0ffd0;border:1px solid #2a5a2a}
            .btn-success:hover{background:#2a5a2a;border-color:#3a7a3a}
            .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
            .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px}
            .field{margin-bottom:16px}
            .field label{display:block;font-size:.85rem;color:#a08a6a;margin-bottom:6px;font-weight:600}
            .field-hint{font-size:.8rem;color:#6b5842;margin-top:4px}
            .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.02em}
            .badge-da{background:rgba(74,42,128,0.2);color:#d0a0ff;border:1px solid rgba(130,80,255,0.3)}
            .badge-upgraded{background:rgba(26,74,26,0.2);color:#80ff80;border:1px solid rgba(50,150,50,0.3)}
            .badge-banned{background:rgba(107,26,26,0.2);color:#ff8080;border:1px solid rgba(200,50,50,0.3)}
            .badge-free{background:rgba(42,42,42,0.2);color:#a0a0a0;border:1px solid rgba(100,100,100,0.3)}
            .flash{padding:12px 20px;border-radius:8px;margin-bottom:24px;background:#1a2a1a;border:1px solid #2a4a2a;color:#a0d080;box-shadow:0 4px 12px rgba(0,0,0,0.2)}
            .flash.error{background:#2a1a1a;border-color:#4a2a2a;color:#d08080}
            .search-row{display:flex;gap:10px;margin-bottom:20px}
            .search-row input{flex:1}
            .toggle-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #2a1f12}
            .toggle-row:last-child{border-bottom:none}
            .toggle-row label{flex:1;cursor:pointer;font-weight:500}
            .toggle-row .hint{font-size:.8rem;color:#6b5842}
            @media (max-width: 640px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }

        </style>
        <script>
        let dfSearchTimer = {};
        function dfSearchItems(cId) {
            clearTimeout(dfSearchTimer[cId]);
            dfSearchTimer[cId] = setTimeout(async () => {
                const q = document.getElementById('isearch-' + cId).value.trim();
                const box = document.getElementById('iresults-' + cId);
                if (q.length < 2) { box.style.display = 'none'; box.innerHTML = ''; return; }
                const items = await fetch('item-search?q=' + encodeURIComponent(q)).then(r => r.json());
                if (!items.length) {
                    box.innerHTML = '<p style="padding:8px 12px;color:#a89060;font-size:.85rem">No items found.</p>';
                } else {
                    box.innerHTML = items.map(i => {
                        const stack = i.maxStackSize > 1 ? ' <span style="color:#7a6040;font-size:.78rem">(stack ×' + i.maxStackSize + ')</span>' : '';
                        return '<div style="padding:7px 12px;cursor:pointer;border-bottom:1px solid #2a1a08" onmouseover="this.style.background=\'#221508\'" onmouseout="this.style.background=\'\'" onclick="dfSelectItem(' + cId + ',' + i.id + ',' + JSON.stringify(i.name) + ')">' + i.name + stack + '</div>';
                    }).join('');
                }
                box.style.display = 'block';
            }, 250);
        }
        let dfSelectedItem = {};
        function dfSelectItem(cId, itemId, itemName) {
            dfSelectedItem[cId] = itemName;
            const qty = parseInt(document.getElementById('iqty-' + cId).value) || 1;
            document.getElementById('isearch-' + cId).value = itemName;
            document.getElementById('iresults-' + cId).style.display = 'none';
            document.getElementById('iid-' + cId).value = itemId;
            document.getElementById('iqtyhidden-' + cId).value = qty;
            document.getElementById('iname-' + cId).textContent = 'Give "' + itemName + '" × ' + qty + '?';
            document.getElementById('igive-' + cId).style.display = '';
            document.getElementById('igive-' + cId).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        function dfUpdateQty(cId) {
            const qty = parseInt(document.getElementById('iqty-' + cId).value) || 1;
            const hidden = document.getElementById('iqtyhidden-' + cId);
            if (hidden) hidden.value = qty;
            const nameSpan = document.getElementById('iname-' + cId);
            if (nameSpan && dfSelectedItem[cId]) {
                nameSpan.textContent = 'Give "' + dfSelectedItem[cId] + '" × ' + qty + '?';
            }
        }
        function dfGiveByID(cId) {
            const itemId = prompt("Enter Item ID to give:");
            if (!itemId) return;
            dfSelectItem(cId, itemId, "Item ID #" + itemId);
        }
        function dfCancelGive(cId) {
            document.getElementById('igive-' + cId).style.display = 'none';
            document.getElementById('isearch-' + cId).value = '';
            document.getElementById('iresults-' + cId).style.display = 'none';
            delete dfSelectedItem[cId];
        }

        </script>
        </head>
        <body>
        <header>
            <h1>⚔ DFPS Admin</h1>
            <a href="./">Users</a>
            <a href="settings">Settings</a>
            {$logoutForm}
        </header>
        <div class="container">
            {$body}
        </div>
        </body>
        </html>
        HTML;
    }

    private function loginPage(string $error): string {
        $err = $error ? "<div class=\"flash error\">{$error}</div>" : '';
        return $this->layout('Login', <<<HTML
        <div style="max-width:360px;margin:80px auto">
        <div class="card">
            <h2>Admin Login</h2>
            {$err}
            <form method="post" action="login">
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" autofocus>
                </div>
                <button class="btn" style="width:100%;margin-top:8px">Login</button>
            </form>
        </div>
        </div>
        HTML);
    }

    // ── Endpoints ─────────────────────────────────────────────────────────────

    #[Request(endpoint: '/admin/', inputType: Input::NONE, outputType: Output::HTML)]
    public function dashboard(): string {
        if ($guard = $this->requireAuth()) return $guard;

        $flash = '';
        if (!empty($_SESSION['flash'])) {
            $type  = $_SESSION['flash']['type'] === 'error' ? 'error' : '';
            $flash = "<div class=\"flash {$type}\">{$_SESSION['flash']['msg']}</div>";
            unset($_SESSION['flash']);
        }

        $users = $this->adminService->getAllUsers();
        $count = \count($users);

        $rows = '';
        foreach ($users as $u) {
            $badges = '';
            if ($u->upgraded)  $badges .= '<span class="badge badge-upgraded">Upgraded</span> ';
            if ($u->banned)    $badges .= '<span class="badge badge-banned">Banned</span> ';
            if (!$u->upgraded && !$u->banned) $badges .= '<span class="badge badge-free">Free</span>';
            $rows .= "<tr><td><a href=\"user?id={$u->id}\">{$u->username}</a></td><td>{$u->email}</td><td>{$badges}</td><td>{$u->lastLogin}</td></tr>";
        }

        return $this->layout('Dashboard', <<<HTML
        {$flash}
        <div class="card">
            <h2>Users ({$count})</h2>
            <form method="get" action="./" class="search-row">
                <input name="q" placeholder="Search username or email…" value="">
                <button class="btn">Search</button>
            </form>
            <table>
                <thead><tr><th>Username</th><th>Email</th><th>Status</th><th>Last Login</th></tr></thead>
                <tbody>{$rows}</tbody>
            </table>
        </div>
        HTML);
    }

    #[Request(endpoint: '/admin/search', inputType: Input::QUERY, outputType: Output::HTML)]
    public function search(array $input): string {
        if ($guard = $this->requireAuth()) return $guard;

        $q = \trim($input['q'] ?? '');
        $users = $q ? $this->adminService->searchUsers($q) : $this->adminService->getAllUsers();
        $count = \count($users);

        $rows = '';
        foreach ($users as $u) {
            $badges = '';
            if ($u->upgraded)  $badges .= '<span class="badge badge-upgraded">Upgraded</span> ';
            if ($u->banned)    $badges .= '<span class="badge badge-banned">Banned</span> ';
            if (!$u->upgraded && !$u->banned) $badges .= '<span class="badge badge-free">Free</span>';
            $rows .= "<tr><td><a href=\"user?id={$u->id}\">{$u->username}</a></td><td>{$u->email}</td><td>{$badges}</td><td>{$u->lastLogin}</td></tr>";
        }

        $qEsc = \htmlspecialchars($q);
        return $this->layout('Search', <<<HTML
        <div class="card">
            <h2>Results for "{$qEsc}" ({$count})</h2>
            <form method="get" action="search" class="search-row">
                <input name="q" placeholder="Search username or email…" value="{$qEsc}">
                <button class="btn">Search</button>
            </form>
            <table>
                <thead><tr><th>Username</th><th>Email</th><th>Status</th><th>Last Login</th></tr></thead>
                <tbody>{$rows}</tbody>
            </table>
        </div>
        HTML);
    }

    #[Request(endpoint: '/admin/user', inputType: Input::QUERY, outputType: Output::HTML)]
    public function userDetail(array $input): string {
        if ($guard = $this->requireAuth()) return $guard;

        $userId = (int)($input['id'] ?? 0);
        if (!$userId) return $this->layout('Error', '<div class="card"><p>Missing user ID.</p></div>');

        $user   = $this->adminService->getUserById($userId);
        $chars  = $this->adminService->getCharsByUser($user);

        $flash = '';
        if (!empty($_SESSION['flash'])) {
            $type  = $_SESSION['flash']['type'] === 'error' ? 'error' : '';
            $flash = "<div class=\"flash {$type}\">{$_SESSION['flash']['msg']}</div>";
            unset($_SESSION['flash']);
        }

        $upgradedChecked  = $user->upgraded  ? 'checked' : '';
        $specialChecked   = $user->special   ? 'checked' : '';
        $bannedChecked    = $user->banned    ? 'checked' : '';
        $activatedChecked = $user->activated ? 'checked' : '';

        $charCards = '';
        foreach ($chars as $c) {
            $daChecked = $c->dragonAmulet ? 'checked' : '';
            $cId = $c->id;
            $armorVal0 = \base_convert(\substr($c->armor, 0, 1), 36, 10);
            $armorPreview = \substr($c->armor, 0, 10) . '...';
            $charCards .= <<<HTML
            <div class="card">
                <h2>Character: {$c->name} (ID #{$cId})</h2>
                <form method="post" action="char/update">
                    <input type="hidden" name="charId" value="{$cId}">
                    <input type="hidden" name="userId" value="{$userId}">
                    <div class="grid-2">
                        <div class="field"><label>Gold</label><input type="number" name="gold" value="{$c->gold}" min="0"></div>
                        <div class="field"><label>Coins (Dragon Coins)</label><input type="number" name="coins" value="{$c->coins}" min="0"></div>
                        <div class="field"><label>Gems</label><input type="number" name="gems" value="{$c->gems}" min="0"></div>
                        <div class="field"><label>Silver</label><input type="number" name="silver" value="{$c->silver}" min="0"></div>
                        <div class="field"><label>Level (1–90)</label><input type="number" name="level" value="{$c->level}" min="1" max="90"></div>
                        <div class="field"><label>Experience</label><input type="number" name="experience" value="{$c->experience}" min="0"></div>
                        <div class="field"><label>Bag Slots</label><input type="number" name="bagSlots" value="{$c->bagSlots}" min="1"></div>
                        <div class="field"><label>Bank Slots</label><input type="number" name="bankSlots" value="{$c->bankSlots}" min="0"></div>
                    </div>
                    <div class="field" style="margin-top:4px">
                        <label><input type="checkbox" name="dragonAmulet" value="1" {$daChecked} style="width:auto;margin-right:6px">Dragon Amulet</label>
                    </div>
                    <button class="btn-success btn" style="margin-top:12px">Save Character</button>
                </form>
                <div style="border-top:1px solid #3a2510;margin-top:16px;padding-top:16px">
                    <p style="color:#f0a500;font-size:.9rem;margin-bottom:10px">Guardian Armor String</p>
                    <p style="color:#a89060;font-size:.85rem;margin-bottom:8px">strArmor[0]={$armorVal0} &nbsp; ({$armorPreview})</p>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <form method="post" action="char/init-guardian-armor" style="display:inline">
                            <input type="hidden" name="charId" value="{$cId}">
                            <input type="hidden" name="userId" value="{$userId}">
                            <button class="btn" type="submit">Init Guardian Armor (index 0)</button>
                        </form>
                        <form method="post" action="char/set-armor-index" style="display:inline;display:flex;gap:4px;align-items:center">
                            <input type="hidden" name="charId" value="{$cId}">
                            <input type="hidden" name="userId" value="{$userId}">
                            <input type="number" name="armorIndex" placeholder="index" min="0" max="97" style="width:70px">
                            <input type="number" name="armorValue" placeholder="value" min="0" max="35" style="width:70px">
                            <button class="btn" type="submit">Set Index</button>
                        </form>
                    </div>
                </div>
                <div style="border-top:1px solid #362818;margin-top:20px;padding-top:20px">
                    <p style="color:#ffb347;font-size:.9rem;margin-bottom:12px;font-weight:600">Quick Add Medals & Resources</p>
                    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 495, 'Defender\'s Medal')">Defender's Medal</button>
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 18514, 'Timewarped Medal')">Timewarped Medal</button>
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 19272, 'Proclamation Medal (SH)')">Proclamation Medal (SH)</button>
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 19924, 'Proclamation Medal (DW)')">Proclamation Medal (DW)</button>
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 3540, 'Shadow Token')">Shadow Token</button>
                    </div>

                    <p style="color:#ffb347;font-size:.9rem;margin-bottom:12px;font-weight:600">Merge Components</p>
                    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 864, 'Elemental Essence')">Elemental Essence</button>
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 913, 'Unlucky Doom Essence')">Unlucky Doom Essence</button>
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 14975, 'Shadow Shard')">Shadow Shard</button>
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 817, 'Wind Seal Fragment')">Wind Seal Fragment</button>
                        <button type="button" class="btn" style="font-size:0.8rem;padding:5px 12px" onclick="dfSelectItem({$cId}, 707, 'Token of Affection')">Token of Affection</button>
                    </div>

                    <p style="color:#ffb347;font-size:.9rem;margin-bottom:12px;font-weight:600">Give Item</p>
                    <div style="display:flex;gap:10px;margin-bottom:8px">
                        <input type="text" id="isearch-{$cId}" placeholder="Search item name…" oninput="dfSearchItems({$cId})" autocomplete="off" style="flex:1">
                        <input type="number" id="iqty-{$cId}" value="1" min="1" max="10000" style="width:100px" placeholder="Qty" oninput="dfUpdateQty({$cId})">
                        <button type="button" class="btn" onclick="dfGiveByID({$cId})" title="Give by Item ID">By ID</button>
                    </div>
                    <div id="iresults-{$cId}" style="background:#0a0704;border:1px solid #4a3825;border-radius:6px;max-height:220px;overflow-y:auto;display:none;margin-bottom:10px;box-shadow:0 4px 12px rgba(0,0,0,0.3)"></div>
                    <form method="post" action="char/give-item" id="igive-{$cId}" style="display:none;margin-top:12px">
                        <input type="hidden" name="charId" value="{$cId}">
                        <input type="hidden" name="userId" value="{$userId}">
                        <input type="hidden" name="itemId" id="iid-{$cId}">
                        <input type="hidden" name="quantity" id="iqtyhidden-{$cId}">
                        <div style="display:flex;align-items:center;gap:12px;background:rgba(255,179,71,0.05);border:1px solid rgba(255,179,71,0.2);padding:10px 16px;border-radius:8px">
                            <span id="iname-{$cId}" style="flex:1;font-size:.95rem;font-weight:500;color:#ffb347"></span>
                            <button type="submit" class="btn btn-success">Confirm Give</button>
                            <button type="button" class="btn" onclick="dfCancelGive({$cId})">Cancel</button>
                        </div>
                    </form>
                </div>

            </div>
            HTML;
        }

        if (!$charCards) {
            $charCards = '<div class="card"><p style="color:#a89060">No characters on this account.</p></div>';
        }

        return $this->layout("User: {$user->username}", <<<HTML
        {$flash}
        <div class="card">
            <h2>Account: {$user->username}</h2>
            <p style="color:#a89060;margin-bottom:16px">ID #{$user->id} · {$user->email} · Joined {$user->createdAt}</p>
            <form method="post" action="user/update">
                <input type="hidden" name="userId" value="{$userId}">
                <div style="display:flex;gap:24px;flex-wrap:wrap">
                    <label><input type="checkbox" name="upgraded" value="1" {$upgradedChecked} style="width:auto;margin-right:6px">Dragon Amulet (Upgraded)</label>
                    <label><input type="checkbox" name="special" value="1" {$specialChecked} style="width:auto;margin-right:6px">Guardian (Special)</label>
                    <label><input type="checkbox" name="activated" value="1" {$activatedChecked} style="width:auto;margin-right:6px">Email Activated</label>
                    <label><input type="checkbox" name="banned" value="1" {$bannedChecked} style="width:auto;margin-right:6px">Banned</label>
                </div>
                <button class="btn" style="margin-top:14px">Save Account</button>
            </form>
        </div>
        {$charCards}
        HTML);
    }

    #[Request(endpoint: '/admin/char/update', inputType: Input::FORM, outputType: Output::HTML)]
    public function updateChar(array $input): string {
        if ($guard = $this->requireAuth()) return $guard;

        $charId = (int)($input['charId'] ?? 0);
        $userId = (int)($input['userId'] ?? 0);

        $this->adminService->updateChar($charId, [
            'gold'         => (int)$input['gold'],
            'coins'        => (int)$input['coins'],
            'gems'         => (int)$input['gems'],
            'silver'       => (int)$input['silver'],
            'level'        => \max(1, \min(90, (int)$input['level'])),
            'experience'   => (int)$input['experience'],
            'bagSlots'     => \max(1, (int)$input['bagSlots']),
            'bankSlots'    => \max(0, (int)$input['bankSlots']),
            'dragonAmulet' => isset($input['dragonAmulet']) ? 1 : 0,
        ]);

        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Character updated successfully.'];
        \http_response_code(302);
        \header("Location: ../user?id={$userId}");
        return '';
    }

    #[Request(endpoint: '/admin/user/update', inputType: Input::FORM, outputType: Output::HTML)]
    public function updateUser(array $input): string {
        if ($guard = $this->requireAuth()) return $guard;

        $userId = (int)($input['userId'] ?? 0);

        $this->adminService->updateUser($userId, [
            'upgraded'  => isset($input['upgraded'])  ? 1 : 0,
            'special'   => isset($input['special'])   ? 1 : 0,
            'activated' => isset($input['activated']) ? 1 : 0,
            'banned'    => isset($input['banned'])    ? 1 : 0,
        ]);

        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Account updated successfully.'];
        \http_response_code(302);
        \header("Location: ../user?id={$userId}");
        return '';
    }

    #[Request(endpoint: '/admin/settings', inputType: Input::NONE, outputType: Output::HTML)]
    public function settings(): string {
        if ($guard = $this->requireAuth()) return $guard;

        $flash = '';
        if (!empty($_SESSION['flash'])) {
            $type  = $_SESSION['flash']['type'] === 'error' ? 'error' : '';
            $flash = "<div class=\"flash {$type}\">{$_SESSION['flash']['msg']}</div>";
            unset($_SESSION['flash']);
        }

        $s = $this->adminService->getSettings();
        $v = fn(string $k) => \htmlspecialchars((string)($s[$k] ?? ''));
        $chk = fn(string $k) => ($s[$k] ?? 0) ? 'checked' : '';

        return $this->layout('Settings', <<<HTML
        {$flash}
        <form method="post" action="settings/update">

        <div class="card">
            <h2>Rates &amp; Rewards</h2>
            <div class="grid-3">
                <div class="field"><label>Gold Multiplier</label><input type="number" name="goldMultiplier" value="{$v('goldMultiplier')}" step="0.1" min="0"><p class="field-hint">Default: 1×</p></div>
                <div class="field"><label>Experience Multiplier</label><input type="number" name="experienceMultiplier" value="{$v('experienceMultiplier')}" step="0.1" min="0"><p class="field-hint">Default: 1×</p></div>
                <div class="field"><label>Gems Multiplier</label><input type="number" name="gemsMultiplier" value="{$v('gemsMultiplier')}" step="0.1" min="0"><p class="field-hint">Default: 1×</p></div>
                <div class="field"><label>Silver Multiplier</label><input type="number" name="silverMultiplier" value="{$v('silverMultiplier')}" step="0.1" min="0"><p class="field-hint">Default: 1×</p></div>
                <div class="field"><label>Daily Quest Coins Reward</label><input type="number" name="dailyQuestCoinsReward" value="{$v('dailyQuestCoinsReward')}" min="0"><p class="field-hint">Dragon Coins per daily quest</p></div>
                <div class="field"><label>Online Threshold (minutes)</label><input type="number" name="onlineThreshold" value="{$v('onlineThreshold')}" min="1"><p class="field-hint">Inactivity before shown as offline</p></div>
            </div>
        </div>

        <div class="card">
            <h2>Account Limits</h2>
            <div class="grid-2">
                <div>
                    <p style="color:#a89060;font-size:.85rem;margin-bottom:10px">Free accounts</p>
                    <div class="field"><label>Max Characters</label><input type="number" name="nonUpgradedChars" value="{$v('nonUpgradedChars')}" min="1"></div>
                    <div class="field"><label>Max Bag Slots</label><input type="number" name="nonUpgradedMaxBagSlots" value="{$v('nonUpgradedMaxBagSlots')}" min="1"></div>
                    <div class="field"><label>Max Bank Slots</label><input type="number" name="nonUpgradedMaxBankSlots" value="{$v('nonUpgradedMaxBankSlots')}" min="0"></div>
                    <div class="field"><label>Max House Slots</label><input type="number" name="nonUpgradedMaxHouseSlots" value="{$v('nonUpgradedMaxHouseSlots')}" min="0"></div>
                    <div class="field"><label>Max House Item Slots</label><input type="number" name="nonUpgradedMaxHouseItemSlots" value="{$v('nonUpgradedMaxHouseItemSlots')}" min="0"></div>
                </div>
                <div>
                    <p style="color:#a89060;font-size:.85rem;margin-bottom:10px">Dragon Amulet (upgraded) accounts</p>
                    <div class="field"><label>Max Characters</label><input type="number" name="upgradedChars" value="{$v('upgradedChars')}" min="1"></div>
                    <div class="field"><label>Max Bag Slots</label><input type="number" name="upgradedMaxBagSlots" value="{$v('upgradedMaxBagSlots')}" min="1"></div>
                    <div class="field"><label>Max Bank Slots</label><input type="number" name="upgradedMaxBankSlots" value="{$v('upgradedMaxBankSlots')}" min="0"></div>
                    <div class="field"><label>Max House Slots</label><input type="number" name="upgradedMaxHouseSlots" value="{$v('upgradedMaxHouseSlots')}" min="0"></div>
                    <div class="field"><label>Max House Item Slots</label><input type="number" name="upgradedMaxHouseItemSlots" value="{$v('upgradedMaxHouseItemSlots')}" min="0"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Server Behaviour</h2>
            <div class="toggle-row"><input type="checkbox" name="dragonAmuletForAll" value="1" {$chk('dragonAmuletForAll')} style="width:auto"><label>Dragon Amulet for all players</label><span class="hint">Gives every account DA regardless of upgrade status</span></div>
            <div class="toggle-row"><input type="checkbox" name="enableAdvertising" value="1" {$chk('enableAdvertising')} style="width:auto"><label>Enable advertising</label><span class="hint">Show ads in game client</span></div>
            <div class="toggle-row"><input type="checkbox" name="canDeleteUpgradedChar" value="1" {$chk('canDeleteUpgradedChar')} style="width:auto"><label>Allow deleting upgraded characters</label></div>
            <div class="toggle-row"><input type="checkbox" name="revalidateClientValues" value="1" {$chk('revalidateClientValues')} style="width:auto"><label>Revalidate client values</label><span class="hint">Recalculate stats server-side</span></div>
            <div class="toggle-row"><input type="checkbox" name="banInvalidClientValues" value="1" {$chk('banInvalidClientValues')} style="width:auto"><label>Ban on invalid client values</label><span class="hint">Auto-ban if server detects cheating</span></div>
        </div>

        <div class="card">
            <h2>Server Info &amp; Messages</h2>
            <div class="field"><label>Server Name</label><input type="text" name="serverName" value="{$v('serverName')}"></div>
            <div class="field"><label>News (shown on login screen)</label><textarea name="news" rows="4">{$v('news')}</textarea></div>
            <div class="field"><label>Sign-up Message</label><textarea name="signUpMessage" rows="3">{$v('signUpMessage')}</textarea></div>
        </div>

        <div class="card">
            <h2>Email</h2>
            <div class="toggle-row" style="margin-bottom:12px"><input type="checkbox" name="sendEmails" value="1" {$chk('sendEmails')} style="width:auto"><label>Send emails (password recovery etc.)</label></div>
            <div class="grid-2">
                <div class="field"><label>API URL</label><input type="text" name="emailApiUrl" value="{$v('emailApiUrl')}"></div>
                <div class="field"><label>API Token</label><input type="text" name="emailApiToken" value="{$v('emailApiToken')}"></div>
                <div class="field"><label>From Address</label><input type="email" name="emailAddress" value="{$v('emailAddress')}"></div>
            </div>
        </div>

        <button class="btn-success btn" style="margin-bottom:32px">Save Settings</button>
        </form>
        HTML);
    }

    #[Request(endpoint: '/admin/settings/update', inputType: Input::FORM, outputType: Output::HTML)]
    public function updateSettings(array $input): string {
        if ($guard = $this->requireAuth()) return $guard;

        $bools = ['dragonAmuletForAll', 'enableAdvertising', 'canDeleteUpgradedChar',
                  'revalidateClientValues', 'banInvalidClientValues', 'sendEmails'];
        $floats = ['goldMultiplier', 'experienceMultiplier', 'gemsMultiplier', 'silverMultiplier'];
        $ints   = ['dailyQuestCoinsReward', 'onlineThreshold',
                   'nonUpgradedChars', 'upgradedChars',
                   'nonUpgradedMaxBagSlots', 'upgradedMaxBagSlots',
                   'nonUpgradedMaxBankSlots', 'upgradedMaxBankSlots',
                   'nonUpgradedMaxHouseSlots', 'upgradedMaxHouseSlots',
                   'nonUpgradedMaxHouseItemSlots', 'upgradedMaxHouseItemSlots'];

        $fields = [];
        foreach ($bools  as $k) $fields[$k] = isset($input[$k]) ? 1 : 0;
        foreach ($floats as $k) $fields[$k] = isset($input[$k]) ? \max(0.0, (float)$input[$k]) : 1.0;
        foreach ($ints   as $k) $fields[$k] = isset($input[$k]) ? \max(0, (int)$input[$k]) : 0;

        foreach (['serverName', 'news', 'signUpMessage', 'emailApiUrl', 'emailApiToken', 'emailAddress'] as $k) {
            if (isset($input[$k])) $fields[$k] = $input[$k];
        }

        $this->adminService->updateSettings($fields);

        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Settings saved.'];
        \http_response_code(302);
        \header('Location: ../settings');
        return '';
    }

    #[Request(endpoint: '/admin/item-search', inputType: Input::QUERY, outputType: Output::JSON)]
    public function itemSearch(array $input): array {
        if (!$this->isLoggedIn()) return [];
        $items = $this->adminService->searchItems($input['q'] ?? '');
        return \array_map(fn($i) => [
            'id'           => $i->id,
            'name'         => $i->name,
            'description'  => $i->description,
            'maxStackSize' => $i->maxStackSize,
        ], $items);
    }

    #[Request(endpoint: '/admin/char/init-guardian-armor', inputType: Input::FORM, outputType: Output::HTML)]
    public function initGuardianArmor(array $input): string {
        if ($guard = $this->requireAuth()) return $guard;

        $charId = (int)($input['charId'] ?? 0);
        $userId = (int)($input['userId'] ?? 0);

        try {
            $this->adminService->initGuardianArmor($charId);
            $_SESSION['flash'] = ['type' => 'ok', 'msg' => "Guardian armor initialized for character #{$charId}."];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Failed: ' . \htmlspecialchars($e->getMessage())];
        }

        \http_response_code(302);
        \header("Location: ../user?id={$userId}");
        return '';
    }

    #[Request(endpoint: '/admin/char/set-armor-index', inputType: Input::FORM, outputType: Output::HTML)]
    public function setArmorIndex(array $input): string {
        if ($guard = $this->requireAuth()) return $guard;

        $charId = (int)($input['charId'] ?? 0);
        $userId = (int)($input['userId'] ?? 0);
        $index  = (int)($input['armorIndex'] ?? 0);
        $value  = (int)($input['armorValue'] ?? 0);

        try {
            $this->adminService->setArmorIndex($charId, $index, $value);
            $_SESSION['flash'] = ['type' => 'ok', 'msg' => "Armor string index {$index} set to {$value} for character #{$charId}."];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Failed: ' . \htmlspecialchars($e->getMessage())];
        }

        \http_response_code(302);
        \header("Location: ../user?id={$userId}");
        return '';
    }

    #[Request(endpoint: '/admin/char/give-item', inputType: Input::FORM, outputType: Output::HTML)]
    public function giveItem(array $input): string {
        if ($guard = $this->requireAuth()) return $guard;

        $charId   = (int)($input['charId']   ?? 0);
        $userId   = (int)($input['userId']   ?? 0);
        $itemId   = (int)($input['itemId']   ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);

        try {
            $this->adminService->giveItemToChar($charId, $itemId, $quantity);
            $item = $this->adminService->getItemById($itemId);
            $_SESSION['flash'] = ['type' => 'ok', 'msg' => "Gave {$quantity}× {$item->name} to character #{$charId}."];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Failed to give item: ' . \htmlspecialchars($e->getMessage())];
        }

        \http_response_code(302);
        \header("Location: ../user?id={$userId}");
        return '';
    }

    #[Request(endpoint: '/admin/login', inputType: Input::FORM, outputType: Output::HTML)]
    public function login(array $input): string {
        $this->startSession();
        if (($input['password'] ?? '') === $this->adminPassword()) {
            $_SESSION['admin_authed'] = true;
            \http_response_code(302);
            \header('Location: ./');
            return '';
        }
        return $this->loginPage('Incorrect password.');
    }

    #[Request(endpoint: '/admin/logout', inputType: Input::NONE, outputType: Output::HTML)]
    public function logout(): string {
        $this->startSession();
        $_SESSION = [];
        \session_destroy();
        \http_response_code(302);
        \header('Location: ./');
        return '';
    }
}
