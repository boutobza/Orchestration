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
# lancement du script qui permet de kill le reverse proxy CHP au meme temps que notre serveur.py
    subprocess.Popen(["bash", "/var/www/pageDeGestion/html/scripts/scriptToKillCHP"])
    print("CHP présent sur le serveur Frontal est terminé !")
# Lancement du script qui permet de kill reverse proxy CHP present sur serveur DockerEngine
    cmd = subprocess.Popen(["/usr/bin/ansible-playbook", "-i", "/etc/ansible/hosts", "/var/www/pageDeGestion/html/playbooks/killCHP.yml"])
    cmd.communicate()
    sys.exit(0)

hote = ''
port = 12800

# Lancement du reverse proxy CHP present sur serveur frontal
subprocess.Popen(["bash", "/var/www/pageDeGestion/html/scripts/scriptLaunchCHP"])
# Lancement du reverse proxy CHP present sur serveur DockerEngine
subprocess.Popen(["/usr/bin/ansible-playbook", "-i", "/etc/ansible/hosts", "/var/www/pageDeGestion/html/playbooks/launchCHP.yml"])

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
    removeRouteFromCHP = False
    waitForCmdToFinish = True

    connexion_avec_client, infos_connexion = connexion_principale.accept()
    msg_recu = connexion_avec_client.recv(1024)
    print("",msg_recu)
    #on décode le message recu en un tableau 
    data = json.loads(msg_recu)
#############################################REFRESH###################################################
    if data[0] == "refresh":
        getContainersInfo = True
        waitForCmdToFinish = False
###############################################CREATE###################################################
    elif data[0] == "create":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/createContainer.yml",
            "-e",
            "nb={0}".format(data[1]),
            "-e",
            "image={0}".format(data[2])])
###############################################START####################################################
    elif data[0] == "start":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/startContainer.yml",
            "-e",
            "id={0}".format(data[1])])
###############################################STOP#####################################################
    elif data[0] == "stop":
        removeRouteFromCHP = True
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/stopContainer.yml",
            "-e",
            "id={0}".format(data[1])])
##############################################DESTROY###################################################       
    elif data[0] == "destroy":
        removeRouteFromCHP = True
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/destroyContainer.yml",
            "-e",
            "id={0}".format(data[1])])
##############################################TERMINAL##################################################
    elif data[0] == "terminal":
        getContainersInfo = False
        getImagesList = False
        # ajout d'une nouvelle route (url) au CHP present sur le serveur frontal pour le conteneur qui a l'ID contenu dans data[1].
        subprocess.Popen(["bash", "/var/www/pageDeGestion/html/scripts/scriptADDRouteToCHP", data[1]])
        print('Nouvelle route est ajouté au CHP présent sur le serveur Frontal')
        # ajout d'une nouvelle route au CHP present sur le serveur DockerEngine.
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/createRouteInCHP.yml",
            "-e",
            "containerID={0}".format(data[1]),
            "-e",
            "containerIP={0}".format(data[2])])
##############################################BUILD_IMAGE###############################################
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
############################################START_SELECTION#############################################
    elif data[0] == "start_selection":
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/startSelection.yml",
            "-e",
            "selection={0}".format(data[1])])
############################################STOP_SELECTION#############################################
    elif data[0] == "stop_selection":
        #removeRouteFromCHP = True
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/stopSelection.yml",
            "-e",
            "selection={0}".format(data[1])])
##########################################DESTROY_SELECTION#############################################
    elif data[0] == "destroy_selection":
        #removeRouteFromCHP = True
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/destroySelection.yml",
            "-e",
            "selection={0}".format(data[1])])
##############################################DELETE_IMAGE##############################################
    elif data[0] == "delete_img":
        getContainersInfo = False
        getImagesList = True

        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/deleteImage.yml",
            "-e",
            "image_name={0}".format(data[1])])
########################################################################################################

    #on attend que la commande c ansible se termine
    if waitForCmdToFinish == True:
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

    if removeRouteFromCHP == True:
        subprocess.Popen(["bash", "/var/www/pageDeGestion/html/scripts/scriptRemoveRouteFromCHP", data[1]])
        print('Route(s) est supprimée(s) du CHP présent sur le serveur Frontal avec succès')
        # supp de la route au CHP present sur le serveur DockerEngine.
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/removeRouteFromCHP.yml",
            "-e",
            "containerID={0}".format(data[1])])
        c.communicate();

    #la commande suivante envoie ok (peu importe ce que l'on envoie)  à la page php qui est normalement en train d'attendre
    connexion_avec_client.send(b"ok")
    print("FIN commandes Ansible")
    connexion_avec_client.close()
print("Fermeture de la connexion")
connexion_principale.close()
