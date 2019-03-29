# -*- coding: utf-8 -*

import socket
import subprocess
import time
import json
import signal
import sys
import xml.etree.ElementTree as ET

#methode pour detruire server.py, CHP_Frontal, CHP_DockerEngine
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
#methode pour recuperer les infos des conteneurs

def getContainersinfos():
        cmd = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/getContainersInfo.yml"])
    #on attend que la commande cmd se finisse au cas ou ^^
        cmd.communicate()

#methode pour parser le fichier retour.xml pour recuperer containerIDs & containerIPs pour les utliser lorsque on lance le terminal

def addFrontalCHPRoutes(containerIDs, containerTokens):
        # ajout d'une nouvelle route (url) au CHP present sur le serveur frontal pour le conteneur qui a l'ID contenu dans data[1].
        subprocess.Popen(["bash", "/var/www/pageDeGestion/html/scripts/scriptADDRouteToCHP", containerIDs, containerTokens])
        print('Nouvelle(s) route(s) est ajoutée au CHP présent sur le serveur Frontal')

def addDockerCHPRoutes(nbContainers, containersInfo):
        # ajout d'une nouvelle route au CHP present sur le serveur DockerEngine.
        cmd = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/createRouteInCHP.yml",
            "-e",
            "nbContainers={0}".format(nbContainers),
            "-e",
            "containersInfo={0}".format(containersInfo)])

        cmd.communicate()


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
        getImagesList = True 
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
    elif data[0] == "start_selection" or data[0] == "start":
        waitForCmdToFinish = False
        cmd = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/startSelection.yml",
            "-e",
            "selection={0}".format(data[1])])
        cmd.communicate()

        getContainersinfos()

        xmlTree = ET.parse('/var/www/pageDeGestion/html/user/retour.xml')
        rootTag = xmlTree.getroot()
        length = len(rootTag.getchildren())

#  12 en bas represente le nombre de caractère present dans l'ID d'un conteneur
        if len(data[1]) > 12:

            IDs = data[1]# data[1] contient l'ID ou les IDs des conteneurs
            IDs = IDs[2:-1] # pour supp les deux premier caractères et le dernier aussi
            ids_table = IDs.split() # mettre les IDs conteneurs séparés par des espaces dans tableau
            tokens = data[2]# data[2] contient le token ou les tokens des conteneurs
            tokens = tokens[2:-1] 
            tokens_table = tokens.split()
            containerIDs = ''
            containerIPs = ''
            containerTokens = ''
            nbContainers = 0


            tokens_table_len = len(ids_table)
            k = 0
            while k < tokens_table_len:
                i = 0
                #recherche les IDs des conteneurs dans le fichier retour.xml et récupération de leurs IPs s'ils ont bien sélectionné
                while i < length:
                    if ids_table[k] == rootTag[i][0].text:
                        containerIDs += " "+ ids_table[k]
                        containerIPs += " "+ rootTag[i][4].text
                        containerTokens += " "+ tokens_table[k]
                        nbContainers += 1
                        break
                    else:    
                        i += 1
                k += 1

            containersInfo = '"'+containerIDs+containerIPs+containerTokens+'"'
            addDockerCHPRoutes(nbContainers, containersInfo)
            addFrontalCHPRoutes(containerIDs, containerTokens)

            #cette partie est éxecutée lorsque on sélectionne un seul conteneur
        elif len(data[1]) == 12:
            containerID = data[1]
            containerToken = data[2]
            containerInfos = '"'
            i = 0
            while i < length: 
                if containerID == rootTag[i][0].text:
                    containerInfos += " "+containerID+" "+rootTag[i][4].text+" "+containerToken
                    break
                else:
                    i += 1
            containerInfos += '"'
            addDockerCHPRoutes(1, containerInfos)
            addFrontalCHPRoutes(containerID, containerToken)



############################################STOP_SELECTION#############################################
    elif data[0] == "stop_selection" or data[0] == "stop":
        removeRouteFromCHP = True
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/stopSelection.yml",
            "-e",
            "selection={0}".format(data[1])])
        if data[0] == "stop_selection":
            data[1] = data[1][2:-1]
            data[1] = '"'+str(data[3])+' '+data[1]+'"'
##########################################DESTROY_SELECTION#############################################
    elif data[0] == "destroy_selection" or data[0] == "destroy":
        removeRouteFromCHP = True
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/destroySelection.yml",
            "-e",
            "selection={0}".format(data[1])])
        if data[0] == "destroy_selection":
            data[1] = data[1][2:-1]
            data[1] = '"'+str(data[3])+' '+data[1]+'"'
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
        getContainersinfos()
    if removeRouteFromCHP == True:
        subprocess.Popen(["bash", "/var/www/pageDeGestion/html/scripts/scriptRemoveRouteFromCHP", data[1], data[2]])
        print('Route(s) est supprimée(s) du CHP présent sur le serveur Frontal avec succès')
        # supp de la route au CHP present sur le serveur DockerEngine.
        c = subprocess.Popen(["/usr/bin/ansible-playbook",
            "-i",
            "/etc/ansible/hosts",
            "/var/www/pageDeGestion/html/playbooks/removeRouteFromCHP.yml",
            "-e",
            "containerID={0}".format(data[1]),
            "-e",
            "containerToken={0}".format(data[2])])
        c.communicate();

    #la commande suivante envoie ok (peu importe ce que l'on envoie)  à la page php qui est normalement en train d'attendre
    connexion_avec_client.send(b"ok")
    print("FIN commandes Ansible")
    connexion_avec_client.close()
print("Fermeture de la connexion")
connexion_principale.close()
