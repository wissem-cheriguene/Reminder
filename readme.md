# Projet Reminder

Application qui a pour but de programmer des rappels par mail avec l'API Sendinblue. 
Les rappels sont **'trigger' automatiquement** via un programmateur de tâche [Cron](https://doc.ubuntu-fr.org/cron) qui déclenche le script Symfony(créé via un commande : **bin/console app:send-mail**) à intervalle régulier.

![homepage](screenshot.png)
## Installation

```bash
git clone <project_name>
composer i
```

## Configuration de l'API Sendinblue

- Créer un compte gratuit (limité à **300 mails/jour**)
- Dans l'onglet Senders&IPs de son compte, s'assurer que l'email est bien vérifié (*le rappel sera envoyé à partir de cet email*)
- Récupérer la **clé API** fourni
- Ajouter une variable d'environnement **api_key** avec votre clé, elle sera ensuite référencé dans le fichier de config *services.yaml*

## Paramétrage de la tâche CRON *sur Linux*

- Ouvrir le CRON `crontab - e`
- Ajouter au fichier la ligne suivante pour déclencher le script à **chaque minute**
```
* * * * * /usr/bin/php /var/www/html/reminder/bin/console app:send-mail 2>&1 | logger -t mycmd
```
- Pour debugger ou voir en temps réel le log de la commande `sudo tail -f  /var/log/syslog`

## Problèmes rencontrés

- Datetime valeur de retour du datetimepicker de bootstrap non-compatible avec Symfo : solution trouvé => https://github.com/symfony/symfony/issues/28703

## Notes

- Pour avoir le CSS du datepicker fonctionnel, il faut prendre la *version 3* de bootstrap
https://getdatepicker.com/4/



