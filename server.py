# -*- coding: utf-8 -*

import socket
import subprocess
import time
import json
import signal
import sys

def signal_handler(sig, frame):
    print("You pressed Ctrl+C")
    print("Fermeture de la connexion")
    connexion_principale.close()
    sys.exit(0)

hote = ''
port = 12800

connexion_principale = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# la ligne suivante est censée éviter l'erreur --> "Address already in use" quand on stop
# le programme avec Ctrl+C et qu'on relance juste après
connexion_principale.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)

connexion_principale.bind((hote, port))
connexion_principale.listen(5)
print("Le serveur écoute à présent sur le port {}".format(port))

# la ligne suivante est censée fermer proprement la connexion quand le programme reçoit
# le signal SIGINT, dans la pratique ça ne résout pas le problème de "Adress already in use"
signal.signal(signal.SIGINT, signal_handler)

while 1:
    getContainersInfo = True
    getImagesList = False

    connexion_avec_client, infos_connexion = connexion_principale.accept()
    msg_recu = connexion_avec_client.recv(1024)
    print("",msg_recu)
    #on décode le message recu en un tableau 
    data = json.loads(msg_recu)

    if data[0] == "destroyall":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/destroyAllContainers.yml"])
    elif data[0] == "create":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/createContainer.yml",
            "-e",
            "nb={0}".format(data[1]),
            "-e",
            "image={0}".format(data[2])])
    elif data[0] == "start":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/startContainer.yml",
            "-e",
            "id={0}".format(data[1])])
    elif data[0] == "stop":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/stopContainer.yml",
            "-e",
            "id={0}".format(data[1])])
    elif data[0] == "destroy":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/destroyContainer.yml",
            "-e",
            "id={0}".format(data[1])])
    elif data[0] == "buildImg":
        getContainersInfo = False
        getImagesList = True

        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/buildDockerImage.yml",
            "-e",
            "file_name={0}".format(data[1]),
            "-e",
            "image_tag={0}".format(data[2])])
    elif data[0] == "start_selection":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/startSelection.yml",
            "-e",
            "selection={0}".format(data[1])])
    elif data[0] == "stop_selection":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/stopSelection.yml",
            "-e",
            "selection={0}".format(data[1])])
    elif data[0] == "destroy_selection":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/destroySelection.yml",
            "-e",
            "selection={0}".format(data[1])])
    elif data[0] == "delete_img":
        getContainersInfo = False
        getImagesList = True

        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/deleteImage.yml",
            "-e",
            "image_name={0}".format(data[1])])


    #on attend que la commande c ansible se termine
    c.communicate()

    if getImagesList == True:
        cmd_recup_img_list = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/getImagesList.yml"])
        cmd_recup_img_list.communicate()
    #la commande suivante recupère les infos des conteneurs
    if getContainersInfo == True:
        p = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/getContainersInfo.yml"])
    #on attend que la commande p se finisse au cas ou ^^
        p.communicate()
    #la commande suivante envoie ok (peu importe ce que l'on envoie)  à la page php qui est normalement en train d'attendre
    connexion_avec_client.send(b"ok")
    print("FIN commandes Ansible")
    connexion_avec_client.close()
print("Fermeture de la connexion")
connexion_principale.close()
