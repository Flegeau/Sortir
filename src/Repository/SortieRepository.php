<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Filter;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Sortie[] Returns an array of All Sortie objects in date limite inscription order of date
     */
    public function findParEtat(string $etat): array {
        return $this->createQueryBuilder("s")
            ->select("s", "e", "l", "c", "p", "o")
            ->join("s.etat", "e")
            ->join("s.lieu", "l")
            ->join("s.campus", "c")
            ->join("s.organisateur", "o")
            ->leftJoin("s.participants", "p")
            ->andWhere('e.libelle = :libelle')
            ->setParameter('libelle', $etat)
            ->getQuery()
            ->getResult(Query::HYDRATE_OBJECT)
            ;
    }

    /**
     * @return Sortie[] Returns an array of All Sortie objects in date limite inscription order of date
     */
    public function findAllOrder(): array {
        return $this->createQueryBuilder("s")
            ->select("s", "e", "l", "c", "p", "o")
            ->join("s.etat", "e")
            ->join("s.lieu", "l")
            ->join("s.campus", "c")
            ->join("s.organisateur", "o")
            ->leftJoin("s.participants", "p")
            ->where("e.libelle != 'Annulée' AND e.libelle != 'Historisée'")
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult(Query::HYDRATE_OBJECT)
            ;
    }

    /**
     * @return Sortie[] Returns an array of All Sortie objects in date limite inscription order of date Where filter
     */
    public function findFilterOrder(Filter $filter, Participant $user): array {
        $query = $this->createQueryBuilder("s")
            ->select("s", "e", "l", "c", "p", "o")
            ->join("s.etat", "e")
            ->join("s.lieu", "l")
            ->join("s.campus", "c")
            ->join("s.organisateur", "o")
            ->leftJoin("s.participants", "p")
            ->where("e.libelle != 'Annulée' AND e.libelle != 'Historisée'");
        if ($filter->getCampus() != null) {
            $query->andWhere('c.nom = :campus')
                ->setParameter('campus', $filter->getCampus()->getNom());
        }
        if ($filter->getNom() != null) {
            $keyword = $filter->getNom();
            $query->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', "%$keyword%");
        }
        if ($filter->getDateStart() != null) {
            $dateStart = $filter->getDateStart()->format('Y-m-d');
            $dateStart .= " 00:00:00.000000";
            $query->andWhere('s.dateHeureDebut >= :dateStart')
                ->setParameter('dateStart', $dateStart);
        }
        if ($filter->getDateEnd() != null) {
            $dateEnd = $filter->getDateStart()->format('Y-m-d');
            $dateEnd .= " 23:59:59.999999";
            $query->andWhere('s.dateHeureDebut <= :dateEnd')
                ->setParameter('dateEnd', $dateEnd);
        }
        if ($filter->getOrganisateur()) {
            $query->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }
        if ($filter->getInscrit()) {
            $query->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }
        if ($filter->getNonInscrit()) {
            $query->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }
        if ($filter->getPassees()) {
            $query->andWhere('e.libelle = :libelle')
            ->setParameter('libelle', "Passée");
        }
        return $query->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult(Query::HYDRATE_OBJECT);
    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function cancel(Sortie $sortie, Etat $etat) {
        $sortie->setEtat($etat);
    }
}
