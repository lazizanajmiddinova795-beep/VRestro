import paramiko

key_path = r'C:\Users\laziza\.ssh\id_ed25519'
key = paramiko.Ed25519Key.from_private_key_file(key_path)

ips = ['100.65.139.1', '100.92.238.113', '100.69.189.125']
users = ['foodflow', 'root', 'ubuntu', 'itcloud', 'hayot', 'laziza']

for ip in ips:
    for u in users:
        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        try:
            ssh.connect(ip, username=u, pkey=key, timeout=3)
            print(f"KEY CONNECTED! {ip} as {u}")
            ssh.close()
        except Exception as e:
            pass
