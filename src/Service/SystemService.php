<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 13/3/19
 * Time: 2:02 PM
 */
namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Entity\System;
use App\Entity\Role;
use App\Entity\City;
use App\Entity\User;
use App\Entity\Location;
use App\Entity\Domain;
use App\Entity\Category;
use App\Entity\SubCategory;

class SystemService
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em,ContainerInterface $c)
    {
        $this->em = $em;
        $this->container = $c;
    }

    public function getStatut()
    {
        $sys= $this->em->getRepository(System::class)->findAll();
        $nb = count($sys);
        if($nb==0)
        {
            return ["message"=>"system empty","statut"=>0];
        }
        else{

            if($nb==1)
            {
                if($sys[0]->getStatut()==true) return ["message"=>"system already initialised","statut"=>1];
                else return ["message"=>"system empty","statut"=>0];
            }
            else{
                return ["message"=>"system error because of multiple entry of system. please drop the database","statut"=>1];
            }

        }
    }

    public function initSystem()
    {
        $town = array(
            array('name' => 'Douala'),
            array('name' => 'Yaoundé'),
            array('name' => 'Bafoussam'),
            array('name' => 'Ringo'),
            array('name' => 'Bamenda'),
            array('name' => 'Ngaoundéré'),
            array('name' => 'Tibati'),
            array('name' => 'Kumba'),
            array('name' => 'Buea'),
            array('name' => 'Bafia'),
            array('name' => 'Kribi'),
            array('name' => 'Limbe'),
            array('name' => 'Mbouda'),
            array('name' => 'Maroua'),
            array('name' => 'Garoua'),
            array('name' => 'Nkongsamba'),
            array('name' => 'Djoum'),
            array('name' => 'Sangmelima'),
            array('name' => 'Ebolowa'),
            array('name' => 'Bertoua'),
            array('name' => 'Loum'),
            array('name' => 'Edéa'),
            array('name' => 'Kumbo'),
            array('name' => 'Foumban'),
            array('name' => 'Dschang'),
            array('name' => 'Bandjoun'),
            array('name' => 'Kousséri'),
            array('name' => 'Maroua'),
            array('name' => 'Guider'),
            array('name' => 'Meiganga'),
            array('name' => 'Batouri'),
            array('name' => 'Yagoua'),
            array('name' => 'Mbalmayo'),
            array('name' => 'Bafang'),
            array('name' => 'Tiko'),
            array('name' => 'Bafia'),
            array('name' => 'Wum'),
            array('name' => 'Kribi'),
            array('name' => 'Foumbot'),
            array('name' => 'Bagangté'),
            array('name' => 'Banyo'),
            array('name' => 'Nkambé'),
            array('name' => 'Bali'),
            array('name' => 'Mbanga'),
            array('name' => 'Mokolo'),
            array('name' => 'Melong'),
            array('name' => 'Manjo'),
            array('name' => 'Mora'),
            array('name' => 'Kaélé'),
            array('name' => 'Tibati'),
            array('name' => 'Ndop'),
            array('name' => 'Akonolinga'),
            array('name' => 'Eseka'),
            array('name' => 'Mamfé'),
            array('name' => 'Obala'),
            array('name' => 'Muyuka'),
            array('name' => 'Nanga-Eboko'),
            array('name' => 'Monatélé'),
            array('name' => 'Abong-Mbang'),
            array('name' => 'Fundong'),
            array('name' => 'Nkoteng'),
            array('name' => 'Fontem'),
            array('name' => 'Mbandjock'),
            array('name' => 'Garoua-Boulai'),
            array('name' => 'Touboro'),
            array('name' => 'Ngaoundal'),
            array('name' => 'Yokadouma'),
            array('name' => 'Pitoa'),
            array('name' => 'Tombel'),
            array('name' => 'Kékem'),
            array('name' => 'Magba'),
            array('name' => 'Bélabo'),
            array('name' => 'Tonga'),
            array('name' => 'Tonga'),
            array('name' => 'Maga'),
            array('name' => 'Koutaba'),
            array('name' => 'Blangoua'),
            array('name' => 'Guidiguis'),
            array('name' => 'Bogo'),
            array('name' => 'Batibo'),
            array('name' => 'Yabassi'),
            array('name' => 'Figuil'),
            array('name' => 'Makénéné'),
            array('name' => 'Gazawa'),
            array('name' => 'Tcholliré'),
            array('name' => 'Oveng'),
            array('name' => 'Mintom'),
            array('name' => 'Mfou'),
            array('name' => 'Ngoumou'),
            array('name' => 'Baham'),
            array('name' => 'Yoko')
        );

        $domains =array(
            array("name"=>"Plomberie",
                "category"=>array(
                    array("name"=>"WC","remark"=>"","sub-category"=>array(
                        array("name"=>"Engorgement","remark"=>"WC bouchés"),
                        array("name"=>"Fuite","remark"=>"recherche de fuite"),
                        array("name"=>"Problème de fonctionnement","remark"=>"cuvette..."),
                        array("name"=>"Changement de WC","remark"=>""),



                    )),
                    array("name"=>"Lavabo / évier","remark"=>"","sub-category"=>array(

                        array("name"=>"Engorgement","remark"=>"lavabo/ évier bouché"),
                        array("name"=>"Fuite / robinetterie","remark"=>""),
                        array("name"=>"Autre","remark"=>""),


                    )),
                    array("name"=>"Douche / baignoire","remark"=>"y compris robinetterie","sub-category"=>array(

                        array("name"=>"Engorgement de douche","remark"=>"bouchée"),
                        array("name"=>"Engorgement de baignoire","remark"=>"bouchée"),
                        array("name"=>"Fuite de douche","remark"=>""),
                        array("name"=>"Fuite de baignoire","remark"=>""),
                        array("name"=>"Autre","remark"=>""),


                    )),
                    array("name"=>"Colonne générale d'immeuble","remark"=>"","sub-category"=>array(

                    )),
                    array("name"=>"Tuyauterie / canalisation","remark"=>"qui n'est pas liée directement à votre WC/lavabo/douche/baignoire ","sub-category"=>array(
                        array("name"=>"D'untuyau d'évacuation","remark"=>"tuyau PVC"),
                        array("name"=>"De la vanne d'entrée. C'est une vanne qui coupe l'eau dans tout l'appartement","remark"=>"directement la vanne ou tout près"),
                        array("name"=>"D'un autre tuyau","remark"=>""),
                        array("name"=>"Je ne vois pas","remark"=>"recherche d'une fuite"),




                    )),
                    array("name"=>"Chaudière / chauffe-eau / ballon d'eau chaude","remark"=>"","sub-category"=>array(

                        array("name"=>"Réparation d'une panne","remark"=>""),
                        array("name"=>"Souscription d'un contrat d'entretien","remark"=>""),
                        array("name"=>"Réparation d'une fuite du ballon d'eau chaude","remark"=>""),
                        array("name"=>"Installation","remark"=>""),
                        array("name"=>"Autre","remark"=>""),



                    )),
                    array("name"=>"Tuyau de la machine à laver/ du lave-vaisselle","remark"=>"","sub-category"=>array(


                    )),
                    array("name"=>"Autre","remark"=>"","sub-category"=>array(




                    )),
                )),
            array("name"=>"Electricité","category"=>array(
                array("name"=>"Prises","remark"=>"","sub-category"=>array(
                    array("name"=>"Prise électrique","remark"=>""),
                    array("name"=>"Plusieurs prises électriques","remark"=>""),
                    array("name"=>"Prise coaxiale","remark"=>"télévision"),
                    array("name"=>"Prise RJ45","remark"=>"Internet"),
                    array("name"=>"Autre","remark"=>""),


                )),
                array("name"=>"Lumière","remark"=>"","sub-category"=>array(
                    array("name"=>"Tout l'appartement","remark"=>""),
                    array("name"=>"Une pièce","remark"=>""),


                )),
                array("name"=>"Radiateur / concervateur / sèche-serviette","remark"=>"","sub-category"=> array(

                    array("name"=>"Réparation d'un concervateur / radiateur électrique ou d'un sèche-serviettes","remark"=>""),
                    array("name"=>"Remplacement d'un convecteur/radiateur électrique ou d'un sèche-serviettes","remark"=>""),
                    array("name"=>"Installation d'un convecteur/radiateur électrique ou d'un sèche-serviettes","remark"=>""),


                )),
                array("name"=>"Tout l'appartement","remark"=>"ni les prises, ni la lumière, ni les appareils électroniques ne fonctionnent","sub-category"=>array(



                )),
                array("name"=>"Tableau électrique","remark"=>"réseau électrique","sub-category"=>array(


                )),
                array("name"=>"Compteur électrique","remark"=>"","sub-category"=>array(
                    array("name"=>"Contacter l'électricien privé","remark"=>""),


                )),
                array("name"=>"Chaudière / chauffe-eau / ballon d'eau chaude","remark"=>"","sub-category"=>array(
                    array("name"=>"Au chauffage","remark"=>""),
                    array("name"=>"A l'eau chaude","remark"=>""),
                    array("name"=>"Au chauffage et à l'eau chaude","remark"=>""),




                )),
                array("name"=>"Installation de détecteur de fumée","remark"=>"","sub-category"=>array(

                )),
                array("name"=>"Constat de bon fonctionnement d'installations électriques","remark"=>"","sub-category"=>array(




                )),
                array("name"=>"Autre","remark"=>"","sub-category"=>array()),

                )),
            array("name"=>"Serrurerie","category"=>array(
                array("name"=>"Porte","remark"=>"","sub-category"=>array(
                    array("name"=>"Porte simple","remark"=>""),
                    array("name"=>"Porte blindée","remark"=>""),
                    array("name"=>"Autre","remark"=>""),

                )),
                array("name"=>"Serrure","remark"=>"y compris ouverture de porte","sub-category"=>array(
                    array("name"=>"Ouvrir la porte","remark"=>""),
                    array("name"=>"Changer la serrure","remark"=>""),
                    array("name"=>"Dégripper la serrure qui s'accroche","remark"=>""),
                    array("name"=>"Autre","remark"=>""),



                )),
                array("name"=>"Volets roulant","remark"=>"","sub-category"=>array(

                    array("name"=>"Volets roulants sont désaxés","remark"=>""),
                    array("name"=>"Volets roulants à réajuster","remark"=>""),
                    array("name"=>"Volets roulants à installer","remark"=>""),
                    array("name"=>"Moteur électrique ne marche plus","remark"=>""),
                    array("name"=>"Autre","remark"=>""),


                )),
                array("name"=>"Rideaux métalliques","remark"=>"","sub-category"=>array(
                    array("name"=>"Rideaux métalliques sont désaxés","remark"=>""),
                    array("name"=>"Rideaux métalliques à installer","remark"=>""),
                    array("name"=>"Moteur électrique ne marche plus","remark"=>""),
                    array("name"=>"Problème de serrure","remark"=>""),
                    array("name"=>"Rideaux sont bloqués","remark"=>""),
                    array("name"=>"Autre","remark"=>""),



                )),
                array("name"=>"Gâche électrique","remark"=>"","sub-category"=>array(

                    array("name"=>"Gâche électrique à réparer","remark"=>""),
                    array("name"=>"Gâche élecrique à installer","remark"=>""),


                )),
                array("name"=>"Autre","remark"=>"","sub-category"=>array()),

            )),
            array("name"=>"Vitrerie","category"=>array(
                array("name"=>"Vitre cassée","remark"=>"","sub-category"=>array(

                    array("name"=>"Simple vitrage","remark"=>""),
                    array("name"=>"Double vitrage","remark"=>""),
                    array("name"=>"Survitrage","remark"=>""),
                    array("name"=>"Autre","remark"=>""),



                )),
                array("name"=>"Fenêtre à remplacer","remark"=>"","sub-category"=>array(
                    array("name"=>"En bois","remark"=>""),
                    array("name"=>"En PVC","remark"=>""),
                    array("name"=>"En aluminium","remark"=>""),
                    array("name"=>"Autre","remark"=>""),



                )),
                array("name"=>"Autre","remark"=>"","sub-category"=>array(

                    array("name"=>"Des joints à remplacer","remark"=>""),
                    array("name"=>"Un trou à percer pour la climatisation","remark"=>""),
                    array("name"=>"Pose d'un aérateur","remark"=>""),
                    array("name"=>"Problème avec une fenêtre de toit","remark"=>"velux"),
                    array("name"=>"Poignée à remplacer","remark"=>"crémone"),
                    array("name"=>"Une fenêtre à raboter","remark"=>""),
                    array("name"=>"Autre","remark"=>""),



                )),

            )),
            array("name"=>"Chauffage","category"=>array(
                array("name"=>"Réparation d'une panne","remark"=>"","sub-category"=>array(

                    array("name"=>"Au chauffage","remark"=>""),
                    array("name"=>"A l'eau chaude","remark"=>""),
                    array("name"=>"Au chauffage et à l'eau chaude","remark"=>""),




                )),
                array("name"=>"Souscription d'un contrat d'entretien","remark"=>"","sub-category"=>array(

                )),
                array("name"=>"Réparation d'une fuite du ballon d'eau chaude","remark"=>"","sub-category"=>array(

                )),
                array("name"=>"Installation","remark"=>"","sub-category"=>array(
                    array("name"=>"Chaudière","remark"=>""),
                    array("name"=>"Chauffe-eau / chauffe-bain","remark"=>""),
                    array("name"=>"Ballon d'eau chaude","remark"=>""),
                    array("name"=>"Radiateur électrique / sèche-serviettes","remark"=>""),




                )),
                array("name"=>"Autre","remark"=>"","sub-category"=>array(

                )),


                )),
            array("name"=>"Electroménager","category"=>array(
                array("name"=>"Le réfrigirateur","remark"=>"","sub-category"=>array(
                    array("name"=>"La porte de réfrigérateur","remark"=>""),
                    array("name"=>"La température dans votre réfrigérateur","remark"=>""),
                    array("name"=>"L'affichage de la température dans un réfrigérateur","remark"=>""),
                    array("name"=>"Le problème de condensation / fuite / givre / ventilation du réfrigérateur","remark"=>""),
                    array("name"=>"La présence d'odeur dans un réfrigérateur","remark"=>""),
                    array("name"=>"Un problème de fonctionnement du réfrigérateur","remark"=>""),
                    array("name"=>"Un autre problème sur votre réfrigérateur","remark"=>""),
                    array("name"=>"Branchement et mise en route du réfrigérateur","remark"=>""),




                )),
                array("name"=>"Le four","remark"=>"","sub-category"=>array(
                    array("name"=>"Classique","remark"=>""),
                    array("name"=>"Four vapeur","remark"=>""),



                )),
                array("name"=>"Les plaques","remark"=>"","sub-category"=>array(
                    array("name"=>"Plaque feux / gaz","remark"=>""),
                    array("name"=>"Plaques vitrocéramiques","remark"=>""),
                    array("name"=>"Plaques à induction","remark"=>""),



                )),
                array("name"=>"La hotte","remark"=>"","sub-category"=>array(

                    array("name"=>"La hotte n'aspire plus","remark"=>""),
                    array("name"=>"Présence de bruit lors de la mise en route de la hotte","remark"=>""),
                    array("name"=>"Le filtre métallique de la hotte est déformé","remark"=>""),
                    array("name"=>"Les boutuns de fonction ne s'enclenchent plus","remark"=>""),
                    array("name"=>"Plus de lumière","remark"=>""),
                    array("name"=>"Je souhaite faire le branchement et la mise en route de ma hotte","remark"=>""),
                    array("name"=>"Autre","remark"=>""),




                )),
                array("name"=>"Le lave-vaisselle","remark"=>"","sub-category"=>array(

                    array("name"=>"La porte du lave-vaisselle","remark"=>""),
                    array("name"=>"Le problème de vidange / odeur /tartre","remark"=>""),
                    array("name"=>"Le problème de fonctionnement du lave-vaisselle","remark"=>""),
                    array("name"=>"La température du lave-vaisselle","remark"=>""),
                    array("name"=>"La fuite d'eau","remark"=>""),
                    array("name"=>"Branchement et mise en route du lave-vaisselle","remark"=>""),
                    array("name"=>"Autre problème de lave-vaisselle","remark"=>""),







                )),
                array("name"=>"Le lave-linge","remark"=>"","sub-category"=>array(

                    array("name"=>"La porte du lave-linge","remark"=>""),
                    array("name"=>"Un problème de fonctionnement du lave-linge","remark"=>""),
                    array("name"=>"Un problème électrique","remark"=>""),
                    array("name"=>"Une fuite d'eau","remark"=>""),
                    array("name"=>"Un problème de vidange du lave-linge","remark"=>""),
                    array("name"=>"Le branchement et la mise en route du lave-linge","remark"=>""),
                    array("name"=>"Un autre problème sur votre lave-linge","remark"=>""),




                )),
                array("name"=>"Le sèche-linge","remark"=>"","sub-category"=>array(

                    array("name"=>"La porte du sèche-linge","remark"=>""),
                    array("name"=>"Un problème de fonctionnement","remark"=>""),
                    array("name"=>"Un problème d'affichage sur la façade de l'appareil","remark"=>""),
                    array("name"=>"Une fuite d'eau","remark"=>""),
                    array("name"=>"Le branchement et la mise en route du sèche-linge","remark"=>""),
                    array("name"=>"Un autre problème sur le sèche-linge","remark"=>""),


                )),
                //array("name"=>"Autre","remark"=>""),

            )),
            array("name"=>"Peinture","category"=>array(
                array("name"=>"Les murs et le plafond","remark"=>"","sub-category"=>array(

                    array("name"=>"Inférieur à 3 mètres","remark"=>""),
                    array("name"=>"Entre 3 à 5 mètres","remark"=>""),


                )),
                array("name"=>"Les murs uniquement","remark"=>"","sub-category"=>array(
                    array("name"=>"Inférieur à 3 mètres","remark"=>""),
                    array("name"=>"Entre 3 à 5 mètres","remark"=>""),


                )),
                array("name"=>"Le plafond uniquement","remark"=>"","sub-category"=>array(

                    array("name"=>"Inférieur à 3 mètres","remark"=>""),
                    array("name"=>"Entre 3 à 5 mètres","remark"=>""),

                )),
               // array("name"=>"Autre","remark"=>""),




            )),
            array("name"=>"Jardinage","category"=>array(
                array("name"=>"Bûcheronnage","remark"=>"","sub-category"=>array(

                    array("name"=>"Tailler","remark"=>""),
                    array("name"=>"Elaguer","remark"=>""),
                    array("name"=>"Abattre","remark"=>""),
                    array("name"=>"Coupe de bois de chauffage","remark"=>""),

                )),
                array("name"=>"Entretien du sol","remark"=>"","sub-category"=> array(
                    array("name"=>"Tondre","remark"=>""),
                    array("name"=>"Désherber","remark"=>""),
                    array("name"=>"Débroussailler","remark"=>""),
                    array("name"=>"Ramassage des feuilles / des déchets verts","remark"=>""),
                    array("name"=>"Aérer votre sol","remark"=>"scarifier"),
                    array("name"=>"Regarnir votre pelouse","remark"=>""),
                    array("name"=>"Semer","remark"=>""),
                    array("name"=>"Pose de pelouse synthétique / rouleau","remark"=>""),
                    array("name"=>"Amender","remark"=>"nourrir le sol"),


                )),
                array("name"=>"Embellissement du jardin","remark"=>"","sub-category"=>array(

                    array("name"=>"Planter","remark"=>""),
                    array("name"=>"Réaliser un potager / massif","remark"=>""),
                    array("name"=>"Déplacer un arbre / un arbuste","remark"=>""),



                )),
                array("name"=>"Remise en état et soins des plantes du jardin","remark"=>"","sub-category"=>array(

                    array("name"=>"Protéger","remark"=>""),
                    array("name"=>"Traiter un massif / un arbuste / un arbre / un potager","remark"=>""),
                    array("name"=>"Remporter une plante / un arbuste / une fleur","remark"=>""),


                )),
                array("name"=>"Montage de mobilier extérieur","remark"=>"","sub-category"=>array(

                    array("name"=>"Un salon de jardin","remark"=>""),
                    array("name"=>"Un barbecue / un hamac / une boite aux lettres","remark"=>""),
                    array("name"=>"Des jeux pour enfant","remark"=>"balançoire,panier,..."),
                    array("name"=>"Un récupérateur d'eau","remark"=>""),
                    array("name"=>"Un étendoir extérieur","remark"=>""),


                )),
                //array("name"=>"Autre","remark"=>""),



            )),
            array("name"=>"Petits travaux","category"=>array(
                array("name"=>"D'une prestation à l'unité","remark"=>"","sub-category"=>array(

                    array("name"=>"Une découoe","remark"=>""),
                    array("name"=>"Un coffrage","remark"=>""),
                    array("name"=>"Une pose et installation","remark"=>""),
                    array("name"=>"Une réparation","remark"=>""),
                    array("name"=>"Un montage / démontage de meuble","remark"=>""),
                    array("name"=>"Un déplacement de meuble","remark"=>""),
                    array("name"=>"Montage de mobile extérieur","remark"=>""),


                )),
                array("name"=>"De plusieurs prestation","remark"=>"","sub-category"=>array(





                )),
                array("name"=>"Autre","remark"=>"","sub-category"=>array()),
            )),

        );

        $role = array("ROLE_CLIENT","ROLE_OPERATOR","ROLE_ADMIN","ROLE_SADMIN","ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON",
            "ROLE_MANAGER_COMPANY","ROLE_SYSTEM"
        );

        foreach ($town as $el)
        {
            $q = new City();
            $q->setName($el["name"]);

            $this->em->persist($q);
        }

        foreach ($role as $el)
        {
            $role = new Role();
            $role->setCode($el);

            $this->em->persist($role);
        }

        foreach ($domains as $d)
        {
            $do = new Domain();

            $do->setName($d["name"]);
            $do->setNameFr($d["name"]);
            $do->setNameEn($d["name"]);
            $do->setIsActive(true);

            foreach ($d["category"] as $c)
            {
                $cat = new Category();
                $cat->setName($c["name"]);
                $cat->setNameFr($c["name"]);
                $cat->setNameEn($c["name"]);
                $cat->setIsActive(true);

                if($c["remark"]!="")
                {
                    $cat->setNote($c["remark"]);
                }



                foreach ($c["sub-category"] as $s)
                {
                    $scat = new SubCategory();
                    $scat->setName($s["name"]);
                    $scat->setNameFr($s["name"]);
                    $scat->setNameEn($s["name"]);
                    $scat->setIsActive(true);

                    if($s["remark"]!="")
                    {
                        $scat->setNote($s["remark"]);
                    }

                    $cat->addSubCategory($scat);
                }

                $do->addCategory($cat);

                $this->em->persist($do);
            }
        }

        $this->em->flush();

        $role = $this->em->getRepository(Role::class)->findOneBy(array("code"=>"ROLE_SADMIN"));
        $role2 = $this->em->getRepository(Role::class)->findOneBy(array("code"=>"ROLE_SYSTEM"));

        if(is_null($role) || is_null($role2) ) return ["message"=>"no role for sdamin or system found","statut"=>0];
        else
        {
            $user = new User();
            $user->setName("sadmin");
            $user->setSurname("digitrav");
            $user->setEmail("sadmin@digitrav.com");
            $user->setGender(true);
            $user->setIsValid(true);
            $user->setPhone("00237699884455");
            $user->setIsClose(false);
            $user->setPassword($this->container->get("security.password_encoder")->encodePassword($user,'123456aA' ));


            $location = new Location();
            $location->setCity("Yaoundé");
            $location->setQuater("Bastos");

            $user->setLocation($location);
            $user->setRole($role);


            $user2 = new User();
            $user2->setName("admin");
            $user2->setSurname("digitrav");
            $user2->setEmail("admin@digitrav.com");
            $user2->setGender(true);
            $user2->setIsValid(true);
            $user2->setPhone("00237699884433");
            $user2->setIsClose(false);
            $user2->setPassword($this->container->get("security.password_encoder")->encodePassword($user2,'123456aA' ));




            $location2 = new Location();
            $location2->setCity("Yaoundé");
            $location2->setQuater("Bastos");

            $user2->setLocation($location2);
            $user2->setRole($role2);

            $this->em->persist($user);
            $this->em->persist($user2);

            $s = new System();
            $s->setStatut(true);
            $this->em->persist($s);

            $this->em->flush();


            return ["message"=>"system initialised","statut"=>1];

        }


    }
}