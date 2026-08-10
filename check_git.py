import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)

stdin, stdout, stderr = ssh.exec_command("cd /home/foodflow/VRestro && git log -n 1 --oneline")
out = stdout.read().decode('utf-8', errors='ignore')
print("REMOTE GIT HEAD:")
print(out)
ssh.close()
