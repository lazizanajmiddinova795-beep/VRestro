import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026')

command = """cd /home/foodflow/VRestro && php artisan tinker --execute="\$foods = \App\Models\Food::where('name', 'like', '%obid%')->get(); foreach(\$foods as \$food) { echo \$food->id . ': ' . \$food->name . '\n'; }" """

stdin, stdout, stderr = ssh.exec_command(command)
print("OUT:", stdout.read().decode())
print("ERR:", stderr.read().decode())
ssh.close()
