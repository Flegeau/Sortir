<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function findAllOrder(): array {
        return $this->createQueryBuilder('s')
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects That the user created
     */
    public function findByCampus($campus): array {
        return $this->createQueryBuilder('s')
            ->join('s.campus', 'c')
            ->addSelect('c') // Entity Campus
            ->where('s.campus = c.id AND c.nom = :campus')
            ->setParameter('campus', $campus)
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects Where dateHeureDebut is after $date
     */
    public function findByDateStart($date): array {
        $date .= " 00:00:00.000000";
        return $this->createQueryBuilder('s')
            ->andWhere("s.dateHeureDebut >= :dateStart")
            ->setParameter('dateStart', date($date))
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects Where dateHeureDebut is before $date
     */
    public function findByDateEnd($date): array {
        $date .= " 23:59:59.999999";
        return $this->createQueryBuilder('s')
            ->andWhere("s.dateHeureDebut <= :dateEnd")
            ->setParameter('dateEnd', date($date))
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects That the user created
     */
    public function findMySortie($user): array {
        return $this->createQueryBuilder('s')
            ->andWhere("s.organisateur = :user")
            ->setParameter('user', $user)
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects Where user is inscrit
     */
    public function findInscrit($user): array {
        return $this->createQueryBuilder('s')
            ->andWhere(':user MEMBER OF s.participants')
            ->setParameter('user', $user)
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects Where user is not inscrit
     */
    public function findNonInscrit($user): array {
        return $this->createQueryBuilder('s')
            ->andWhere(':user NOT MEMBER OF s.participants')
            ->setParameter('user', $user)
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects Where etat is Passée
     */
    public function findPasse(): array {
        return $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->addSelect('e') // Entity Etat
            ->where('s.etat = e.id AND e.libelle = :libelle')
            ->setParameter('libelle', "Passée")
            ->orderBy('s.dateLimiteInscription', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects Where etat is $etat
     */
    public function findSelonEtat(string $etat): array {
        return $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->addSelect('e')
            ->where('s.etat = e.id AND e.libelle = :libelle')
            ->setParameter('libelle', $etat)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects Where nom equal $keyword
     */
    public function search($keyword): array {
        $em = $this->getEntityManager();
        $dql = "
                SELECT s FROM App\Entity\Sortie s
                WHERE s.nom LIKE :nom
                ORDER BY s.dateLimiteInscription DESC
        ";
        $stmt = $em->createQuery($dql);
        $stmt->setParameters(
            array(
                ":nom"=>"%$keyword%"
            ) );
        return $stmt->getResult();
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
}
