<?php

namespace AppBundle\Entity;

use AppBundle\Enum\FriendTypes;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\City;
use AppBundle\Entity\ConnectionRequest;
use Doctrine\ORM\NoResultException;

/**
 * Class ConnectionRequestRepository
 * @package AppBundle\Entity
 */
class ConnectionRequestRepository extends EntityRepository
{
    /**
     * @param ConnectionRequest $connectionRequest
     * @return ConnectionRequest
     */
    public function save(ConnectionRequest $connectionRequest)
    {
        $this->getEntityManager()->persist($connectionRequest);
        $this->getEntityManager()->flush();

        return $connectionRequest;
    }

    /**
     * @param ConnectionRequest $connectionRequest
     */
    public function remove(ConnectionRequest $connectionRequest)
    {
        $this->getEntityManager()->remove($connectionRequest);
        $this->getEntityManager()->flush();
    }

    /**
     * @param User $user
     * @return null|object
     */
    public function findOneByUser(User $user)
    {
        return $this->findOneBy(array('user' => $user, 'disqualified' => false));
    }

    /**
     * @param $userId
     * @return null|object
     */
    public function findOneUnpendingByUserId($userId)
    {
        return $this->findOneBy(array('user' => $userId, 'pending' => false));
    }

    /**
     * @param $userId
     * @return null|object
     */
    public function findOneByUserId($userId)
    {
        return $this->findOneBy(array('user' => $userId));
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this
            ->createQueryBuilder('cr')
            ->where('cr.disqualified     = false')
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param City $city
     * @param $wantToLearn
     * @param $musicFriend
     * @return array
     */
    public function countByCityWantToLearnAndMusicFriend(City $city, $wantToLearn, $musicFriend)
    {
        return $this
            ->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->join('cr.user', 'u')
            ->where('u.wantToLearn         = :wantToLearn')
            ->andWhere('cr.city            = :city')
            ->andWhere('cr.disqualified    = false')
            ->andWhere('cr.pending         = false')
            ->andWhere('u.type = :type')
            ->setParameters([
                'city'          => $city,
                'wantToLearn'   => $wantToLearn,
                'type' => $musicFriend ? FriendTypes::MUSIC : FriendTypes::FRIEND,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param City $city
     * @return array
     */
    public function countNewWithinCity(City $city)
    {
        return $this->countByCityWantToLearnAndMusicFriend($city, true, false);
    }

    /**
     * @param City $city
     * @return array
     */
    public function countNewMusicFriendWithinCity(City $city)
    {
        return $this->countByCityWantToLearnAndMusicFriend($city, true, true);
    }

    /**
     * @param City $city
     * @return array
     */
    public function countEstablishedWithinCity(City $city)
    {
        return $this->countByCityWantToLearnAndMusicFriend($city, false, false);
    }

    /**
     * @param City $city
     * @return array
     */
    public function countEstablishedMusicFriendWithinCity(City $city)
    {
        return $this->countByCityWantToLearnAndMusicFriend($city, false, true);
    }

    /**
     * @param City $city
     * @return array
     */
    public function findCityStats(City $city)
    {
        return $this
            ->createQueryBuilder('cr')
            ->select('SUM(IF(cr.want_to_learn = 0,0,1)) AS new, SUM(IF(cr.want_to_learn = 0,1,0)) AS established')
            ->where('cr.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @param User $user
     * @return int
     */
    public function countUserActiveRequests(User $user)
    {
        $qb = $this->createQueryBuilder('cr');

        $qb
            ->select('COUNT(cr.id)')
            ->where('cr.user = :user')
            ->andWhere('cr.disqualified = false')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param City $city
     * @param $excludeType
     *
     * @return ConnectionRequest[]
     */
    public function findByCity(City $city, $excludeType = null)
    {
        return $this
            ->findByCityQueryBuilder($city, $excludeType)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param City $city
     * @param $excludeType
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findByCityQueryBuilder(City $city, $excludeType = null)
    {
        $qb = $this
            ->createQueryBuilder('cr')
            ->innerJoin('cr.user', 'u')
            ->where('cr.city        = :city')
            ->andWhere('cr.disqualified = false')
            ->andWhere('cr.pending = false')
            ->andWhere('cr.inspected = true')
            ->andWhere('u.enabled = true')
            ->groupBy('cr.user')
            ->orderBy('cr.sortOrder', 'DESC')
            ->addOrderBy('cr.createdAt', 'ASC')
            ->setParameter('city', $city)
        ;
        if ($excludeType) {
            $qb->andWhere('cr.type != :type')->setParameter('type', $excludeType);
        }

        return $qb;
    }

    /**
     * @param User $user
     * @return array
     */
    public function findAllPending(User $user)
    {
        $qb     =  $this
            ->createQueryBuilder('cr')
            ->where('cr.pending  = true')
            ->groupBy('cr.user')
            ->orderBy('cr.sortOrder', 'DESC')
            ->addOrderBy('cr.createdAt', 'ASC')
        ;

        if ($user->getCities()) {
            $qb
                ->andWhere('cr.city IS NULL OR cr.city IN (:cities)')
                ->setParameter('cities', $user->getCities())
            ;
        }
        if ($user->getAdminMunicipalities()) {
            $qb
                ->andWhere('cr.municipality IS NULL OR cr.municipality IN (:municipalities)')
                ->setParameter('municipalities', $user->getAdminMunicipalities())
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param $inspected
     * @return array
     */
    public function findAllByInspected(User $user, $inspected)
    {
        $qb     =  $this
            ->createQueryBuilder('cr')
            ->where('cr.inspected  = :inspected')
            ->groupBy('cr.user')
            ->addOrderBy('cr.createdAt', 'ASC')
            ->setParameter('inspected', $inspected)
        ;

        if ($user->getCities()) {
            $qb
                ->andWhere('cr.city IS NULL OR cr.city IN (:cities)')
                ->setParameter('cities', $user->getCities())
            ;
        }
        if ($user->getAdminMunicipalities()) {
            $qb
                ->andWhere('cr.municipality IS NULL OR cr.municipality IN (:municipalities)')
                ->setParameter('municipalities', $user->getAdminMunicipalities())
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Municipality $municipality
     *
     * @return ConnectionRequest[]
     */
    public function findWantToLearnStartFriendsByMunicipality(Municipality $municipality)
    {
        return $qb = $this->createQueryBuilder('cr')
            ->innerJoin('cr.user', 'u')
            ->where('cr.municipality = :municipality')
            ->andWhere('cr.wantToLearn = true')
            ->andWhere('cr.type = :type')
            ->andWhere('cr.inspected = true')
            ->andWhere('cr.pending = false')
            ->setParameter('municipality', $municipality)
            ->setParameter('type', FriendTypes::START)
            ->getQuery()
            ->execute()
            ;
    }

    /**
     * @param Municipality $municipality
     *
     * @return ConnectionRequest[]
     */
    public function findInspectedNotPendingStartFriendsByMunicipality(Municipality $municipality)
    {
        return $qb = $this->createQueryBuilder('cr')
            ->innerJoin('cr.user', 'u')
            ->where('cr.municipality = :municipality')
            ->andWhere('cr.type = :type')
            ->andWhere('cr.inspected = true')
            ->andWhere('cr.pending = false')
            ->setParameter('municipality', $municipality)
            ->setParameter('type', FriendTypes::START)
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->execute()
            ;
    }
}
