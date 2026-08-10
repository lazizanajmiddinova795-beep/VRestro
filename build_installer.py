import urllib.request, zipfile, os, subprocess

url = 'https://prdownloads.sourceforge.net/nsis/nsis-3.08.zip'
print('Downloading NSIS...')
urllib.request.urlretrieve(url, 'nsis.zip')

print('Extracting NSIS...')
with zipfile.ZipFile('nsis.zip', 'r') as zip_ref:
    zip_ref.extractall('nsis')

nsi_code = r'''
OutFile "public\foodflow-setup.exe"
InstallDir "$PROGRAMFILES\FoodFlow"
Icon "public\favicon.ico"
UninstallIcon "public\favicon.ico"

Section "Install"
  SetOutPath $INSTDIR
  File "public\foodflow-windows.exe"
  
  CreateShortcut "$DESKTOP\FoodFlow.lnk" "$INSTDIR\foodflow-windows.exe"
  CreateShortcut "$SMPROGRAMS\FoodFlow.lnk" "$INSTDIR\foodflow-windows.exe"
  
  WriteUninstaller "$INSTDIR\Uninstall.exe"
SectionEnd

Section "Uninstall"
  Delete "$INSTDIR\foodflow-windows.exe"
  Delete "$INSTDIR\Uninstall.exe"
  Delete "$DESKTOP\FoodFlow.lnk"
  Delete "$SMPROGRAMS\FoodFlow.lnk"
  RMDir "$INSTDIR"
SectionEnd
'''

with open('setup.nsi', 'w') as f:
    f.write(nsi_code)

print('Compiling installer...')
subprocess.run(['nsis\\nsis-3.08\\makensis.exe', 'setup.nsi'])
print('Done!')
