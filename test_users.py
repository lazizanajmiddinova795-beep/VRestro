import paramiko
for ip in ['100.92.238.113', '100.69.189.125']:
    for user in ['foodflow', 'root', 'ubuntu', 'itcloud']:
        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        try:
            ssh.connect(ip, username=user, password='clone2026', timeout=3)
            print(f"CONNECTED! {ip} as {user}")
            ssh.close()
            break
        except Exception as e:
            pass
