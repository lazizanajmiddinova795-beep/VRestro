import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026')

php_code = """
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

$setting = \\App\\Models\\Setting::where('key', 'restaurant_name')->first();
if ($setting) {
    $setting->value = 'Xorazm Gamgurg';
    $setting->save();
    echo "Updated restaurant_name to " . $setting->value;
}
"""

sftp = ssh.open_sftp()
with sftp.open('/home/foodflow/VRestro/fix_name.php', 'w') as f:
    f.write(php_code)
sftp.close()

stdin, stdout, stderr = ssh.exec_command('cd /home/foodflow/VRestro && php fix_name.php')
print("OUT:", stdout.read().decode())
print("ERR:", stderr.read().decode())
ssh.close()
