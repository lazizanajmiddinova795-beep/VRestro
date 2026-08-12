import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026')

# Raw string for PHP code
php_code = r"""
$setting = \App\Models\Setting::first();
if ($setting) {
    $setting->branding_name = 'Xorazm Gamgurg';
    $setting->save();
    echo 'Updated setting name to ' . $setting->branding_name;
} else {
    echo 'No settings found in DB';
}
"""

command = f'cd /home/foodflow/VRestro && php artisan tinker --execute="{php_code}"'

stdin, stdout, stderr = ssh.exec_command(command)
print("OUT:", stdout.read().decode())
print("ERR:", stderr.read().decode())
ssh.close()
