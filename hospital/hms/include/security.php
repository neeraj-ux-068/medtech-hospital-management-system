<?php
/** Shared security helpers for the legacy HMS pages. */

function hms_h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function hms_rich_text($value): string
{
    $allowed = ['p', 'br', 'ul', 'ol', 'li', 'strong', 'em', 'b', 'i'];
    $html = strip_tags((string)($value ?? ''), '<p><br><ul><ol><li><strong><em><b><i>');
    return preg_replace_callback('/<\/?([a-z0-9]+)\b[^>]*>/i', static function ($match) use ($allowed) {
        $tag = strtolower($match[1]);
        if (!in_array($tag, $allowed, true)) {
            return '';
        }
        return str_starts_with($match[0], '</') ? '</' . $tag . '>' : '<' . $tag . '>';
    }, $html) ?? '';
}

function hms_db_query(mysqli $connection, string $sql, array $params = [])
{
    $statement = mysqli_prepare($connection, $sql);
    if ($statement === false) {
        error_log('HMS SQL prepare failed: ' . mysqli_error($connection));
        throw new RuntimeException('Database operation failed.');
    }

    if ($params !== []) {
        $types = '';
        foreach ($params as $value) {
            $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        }
        $bind = [$types];
        foreach ($params as $key => $value) {
            $bind[] = &$params[$key];
        }
        if (!call_user_func_array([$statement, 'bind_param'], $bind)) {
            $statement->close();
            error_log('HMS SQL parameter binding failed.');
            throw new RuntimeException('Database operation failed.');
        }
    }

    if (!$statement->execute()) {
        error_log('HMS SQL execute failed: ' . $statement->error);
        $statement->close();
        throw new RuntimeException('Database operation failed.');
    }

    if ($statement->field_count > 0) {
        $result = $statement->get_result();
        $statement->close();
        return $result;
    }

    $statement->close();
    return true;
}

function hms_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    $current = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $current['path'] ?: '/',
        'domain' => $current['domain'],
        'secure' => !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function hms_login(string $role, int $userId, string $loginName): void
{
    hms_start_session();
    session_regenerate_id(true);
    $_SESSION = [];
    $_SESSION['role'] = $role;
    $_SESSION['id'] = $userId;
    $_SESSION['login'] = $role === 'patient' || $role === 'admin' ? $loginName : '';
    $_SESSION['dlogin'] = $role === 'doctor' ? $loginName : '';
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}

function hms_base_url(): string
{
    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $hmsPosition = strpos($script, '/hms/');
    if ($hmsPosition !== false) {
        return substr($script, 0, $hmsPosition);
    }
    $base = rtrim(dirname($script), '/');
    return $base === '.' ? '' : $base;
}

function hms_require_role(string $role): void
{
    hms_start_session();
    if (($_SESSION['role'] ?? null) !== $role || (int)($_SESSION['id'] ?? 0) < 1) {
        $base = hms_base_url();
        $loginPaths = [
            'patient' => $base . '/hms/user-login.php',
            'doctor' => $base . '/hms/doctor/index.php',
            'admin' => $base . '/hms/admin/index.php',
        ];
        header('Location: ' . ($loginPaths[$role] ?? $base . '/'));
        exit;
    }
}

function hms_csrf_token(): string
{
    hms_start_session();
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function hms_csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . hms_h(hms_csrf_token()) . '">';
}

function hms_verify_csrf(): void
{
    hms_start_session();
    $provided = (string)($_POST['_csrf_token'] ?? '');
    $expected = (string)($_SESSION['_csrf_token'] ?? '');
    if ($provided === '' || $expected === '' || !hash_equals($expected, $provided)) {
        http_response_code(400);
        exit('Your session token is invalid. Reload the page and try again.');
    }
}

function hms_post_string(string $key, int $maxLength = 2000): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    $value = trim($value);
    if (strlen($value) > $maxLength) {
        return '';
    }
    return $value;
}

function hms_post_password(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) && strlen($value) <= 4096 ? $value : '';
}

function hms_valid_new_password(string $password): bool
{
    // PASSWORD_DEFAULT currently uses bcrypt, which only consumes the first 72 bytes.
    return strlen($password) >= 10 && strlen($password) <= 72;
}

function hms_verify_legacy_password(string $plainText, string $stored): bool
{
    $info = password_get_info($stored);
    if (!empty($info['algo'])) {
        return password_verify($plainText, $stored);
    }

    // The shipped database contains both MD5 values and a plaintext admin password.
    if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
        return hash_equals(strtolower($stored), md5($plainText));
    }

    return $stored !== '' && hash_equals($stored, $plainText);
}

function hms_rehash_password(mysqli $connection, string $table, int $id, string $plainText): void
{
    $allowedTables = ['users', 'doctors', 'admin'];
    if (!in_array($table, $allowedTables, true)) {
        throw new InvalidArgumentException('Invalid account table.');
    }
    $hash = password_hash($plainText, PASSWORD_DEFAULT);
    if ($hash === false) {
        throw new RuntimeException('Password update failed.');
    }
    hms_db_query($connection, "UPDATE {$table} SET password = ? WHERE id = ?", [$hash, $id]);
}

function hms_logout(string $role): void
{
    hms_start_session();
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
    header('Location: ' . hms_base_url() . '/');
    exit;
}
