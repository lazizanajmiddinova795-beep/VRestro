import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)

shell = ssh.invoke_shell()
shell.send('grep -n "Buyurtma Turi" /home/foodflow/VRestro/resources/js/components/CashierOrder.vue\n')
import time
time.sleep(2)
out = shell.recv(9999).decode('utf-8')
print("GREP ON SERVER:")
print(out)
ssh.close()
