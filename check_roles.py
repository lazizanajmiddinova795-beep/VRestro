import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)
cmd = 'cd /home/foodflow/VRestro && php artisan tinker --execute="\ = App\\\\Models\\\\User::where(\'email\', \'admin@itcloud.uz\')->first(); echo json_encode([\'roles\' => \->roles->pluck(\'name\'), \'permissions\' => \->permissions->pluck(\'name\')]);"'
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode('utf-8'))
ssh.close()
