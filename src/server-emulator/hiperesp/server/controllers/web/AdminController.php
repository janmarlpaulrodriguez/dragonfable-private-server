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
            body{font-family:system-ui,sans-serif;background:#0f0a02;color:#e8d9b0;min-height:100vh}
            a{color:#f0a500;text-decoration:none}a:hover{text-decoration:underline}
            header{background:#1a1208;border-bottom:2px solid #5a3a10;padding:12px 24px;display:flex;align-items:center;gap:16px}
            header h1{font-size:1.2rem;color:#f0a500;flex:1}
            .container{max-width:960px;margin:32px auto;padding:0 16px}
            .card{background:#1a1208;border:1px solid #3a2510;border-radius:8px;padding:20px;margin-bottom:20px}
            .card h2{color:#f0a500;margin-bottom:16px;font-size:1.1rem;border-bottom:1px solid #3a2510;padding-bottom:8px}
            table{width:100%;border-collapse:collapse}
            th,td{padding:8px 12px;text-align:left;border-bottom:1px solid #2a1a08}
            th{color:#f0a500;font-size:.85rem;text-transform:uppercase;letter-spacing:.05em}
            tr:hover{background:#221508}
            input,select{background:#0f0a02;border:1px solid #3a2510;color:#e8d9b0;padding:6px 10px;border-radius:4px;width:100%}
            input:focus,select:focus{outline:1px solid #f0a500}
            .btn{background:#5a3a10;color:#e8d9b0;border:none;padding:7px 16px;border-radius:4px;cursor:pointer;font-size:.9rem}
            .btn:hover{background:#7a5a20}
            .btn-danger{background:#6b1a1a;color:#e8d9b0;border:none;padding:7px 16px;border-radius:4px;cursor:pointer;font-size:.9rem}
            .btn-danger:hover{background:#8b2a2a}
            .btn-success{background:#1a4a1a;color:#e8d9b0;border:none;padding:7px 16px;border-radius:4px;cursor:pointer;font-size:.9rem}
            .btn-success:hover{background:#2a6a2a}
            .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
            .field{margin-bottom:12px}
            .field label{display:block;font-size:.85rem;color:#a89060;margin-bottom:4px}
            .badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.78rem}
            .badge-da{background:#4a2a80;color:#d0a0ff}
            .badge-upgraded{background:#1a4a1a;color:#80ff80}
            .badge-banned{background:#6b1a1a;color:#ff8080}
            .badge-free{background:#2a2a2a;color:#a0a0a0}
            .flash{padding:10px 16px;border-radius:4px;margin-bottom:16px;background:#2a4a1a;border:1px solid #4a6a2a;color:#a0d080}
            .flash.error{background:#4a1a1a;border-color:#6a2a2a;color:#d08080}
            .search-row{display:flex;gap:8px;margin-bottom:16px}
            .search-row input{flex:1}
        </style>
        </head>
        <body>
        <header>
            <h1>⚔ DFPS Admin</h1>
            <a href="./">Dashboard</a>
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
        $bannedChecked    = $user->banned    ? 'checked' : '';
        $activatedChecked = $user->activated ? 'checked' : '';

        $charCards = '';
        foreach ($chars as $c) {
            $daChecked = $c->dragonAmulet ? 'checked' : '';
            $charCards .= <<<HTML
            <div class="card">
                <h2>Character: {$c->name} (ID #{$c->id})</h2>
                <form method="post" action="char/update">
                    <input type="hidden" name="charId" value="{$c->id}">
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
                    <label><input type="checkbox" name="upgraded" value="1" {$upgradedChecked} style="width:auto;margin-right:6px">Dragon Amulet Account (Upgraded)</label>
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
            'activated' => isset($input['activated']) ? 1 : 0,
            'banned'    => isset($input['banned'])    ? 1 : 0,
        ]);

        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Account updated successfully.'];
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
