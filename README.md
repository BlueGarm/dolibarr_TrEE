ChangeLog
=========

Historique des modifications

* * *

### **Octobre 2024**

*   Modifications du comportement des lignes de commandes dans le module commandes :

*   Masquage des informations complémentaires vides
*   Affichage en rouge, si demande d'ajout du produit dans le système

* * *

### **Août 2024**

Passage en version 19.0.3

*   Mises à jour de sécurité mineurs par MISI suite migration des serveurs
*   Ajout d'information sur l'état des commandes (Visible uniquement par les ITA)
*   Ajout d'un bandeau d'alerte sur les commandes, pour les projets demandant confirmation avant commande
*   Modifications diverses traductions
*   Modifications graphiques mineurs

### **Février 2024**

Passage en version 19

*   Modifications graphiques mineurs

* * *

### **Juillet 2023**

Passage en version 18

*   Traduction du mode maintenance
*   Augmentation du nombre de ligne affichée par les widgets
*   Liste "Commande/Produit Pour" affiche désormais Prénom et Nom
*   Code Nacre affiche maintenant Code + Description général
*   Ajout d'une icône a coté des référence de commande et d'une liste affichés au survol, des fichiers liés à la commande (Bon de commande, Devis, Bon de livraison...), pour les télécharger oules visualiser sans avoir besoin d'aller dans la commande elle même
*   Changement divers icônes
*   Modifications graphiques mineurs

* * *

### **Janvier 2023**

Le site de gestion est passé à la version 16.0.3 (avant 14.0.2), soit plus de deux versions majeures. En plus des correctifs réalisés par les développeurs (failles de sécurité, correction de bugs, amélioration...), pour les curieux, voici les modifications réalisées par mes soins pour l'adapter à notre usage :

*   Nouvelle icône pour les Projets : Diagram => Tirelire (Plus de clarté Projet = Budget)

Ancienne Icône

Nouvelle Icône

*   Affichage du budget restant sous le projet dans l'entête d'une commande si budget saisie sur la fiche projet => alerte si fonds insuffisant (Attention cela prend en toutes les commandes même les brouillons)  
    ![](https://blog-tree.timc.fr/wp-content/uploads/2023/01/Budget_restant.png)
*   Affichage du nombre de produits à (ou restant à) réceptionner d'une commande via l'onglet "Réceptions" (= produits dans le système avec suivi du stock), si produit pas en suivi de stock, il faut passer comme d'habitude par "Classer le Réception", **cela est une aide visuelle  
    ![](https://blog-tree.timc.fr/wp-content/uploads/2023/01/recetpion.png)**
*   Nouveau modèle PDF pour les bons de commande interne :
    *   Optimisation code => Fichier plus léger (~20ko contre +250ko avant).
    *   Affichage de nombreuses informations directement dans l'entête après les notes (Devis, Commun, Numéro de commande, Destinataire de commande)  
        ![](https://blog-tree.timc.fr/wp-content/uploads/2023/01/pdf_infos.png)
    *   Affichage d'informations sur les lignes produit (Code Nacre, Devis, Produit Pour)  
        ![](https://blog-tree.timc.fr/wp-content/uploads/2023/01/pdf_prod.png)
*   Possibilité d'utiliser et de mettre une référence fournisseurs dans les commandes sur un produits dans le système mais non enregistré avec le fournisseur selectionné
*   Correction de divers bugs et optimisation code
*   Divers changements graphique

* * *

### **Avril 2022**

Passage en version 14

*   Integration des budgets, budget restant et affichage dans les listes et fiches projets
*   Mise en forme message sous formulaire de connexion
*   Modifications de quelques traductions
*   Modifications graphiques mineurs

* * *

### **Mai 2021**

Passage en version 13.0.2

*   Ajout et personnalisation du widget "Commande en attente de livraison"

*   Affichage de la date de commande ou de livraison si connue
*   Ajout d'icônes de statut (Alerte, Date Commande, Date Livraison)

*   Suppression des tableaux inutiles dans les fiches projets : Ne reste que les commandes liées au projet
*   Modifications graphiques mineurs

* * *

### **Février 2020**

Passage en version 13

*   Modification de l'ordre d'affiche dans le champs de recherche : Produit en premier
*   Indication d'alerte stock sur les produit "Actif"
*   Ajout icône et indication date de commande
*   Ajout format personnalisé pour les pdf des commandes : Affichage des codes NACRES
*   Référence fournisseur en gras sur pdf des commandes
*   Modification comportement module commandes : Masque ou affiche les codes nacre en fonction du type de produit (libre ou prédéfinit)
*   Changement d'images
*   Affichage des Alertes Retard sur Date de commande/Date de livraison dans les listes et fiches commandes
*   Ajout d'une icône dans le menu haut "Retour blog"
*   Modifications couleurs

* * *

### **Octobre 2020**

Version initiale - V12
