<?php


namespace PTS\SyliusReferralPlugin\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository as BaseCustomerRepository;

class CustomerRepository extends BaseCustomerRepository
{
    public function getEnrolled($enroller, $limit, $currentPage)
    {
        if (!$currentPage) {
            $currentPage = 1;
        }
        $qb = $this->createQueryBuilder('c')
            ->where('c.enroller = :enroller')
            ->setParameter('enroller', $enroller);

        $count = $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.enroller = :enroller')
            ->setParameter('enroller', $enroller)
            ->getQuery()->getSingleScalarResult();

        $maxPages = ceil($count/$limit);
        if ($maxPages == 0) {
            $maxPages = 1;
        }
        if ($maxPages < $currentPage) {
            $currentPage = $maxPages;
        }

        $paginated = $this->paginate(
            $qb,
            $limit,
            $currentPage
        );
        return $paginated;
    }

    public function clearEnrolled($enroller)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where('c.enroller = :enroller')
            ->setParameter('enroller', $enroller)
            ->update(['c.enroller' => null])
            ->getQuery()->execute();
    }

    private function paginate($dql, $limit, $page)
    {
        $paginated = new Paginator($dql);

        $paginated->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginated;
    }

    public function getAllWaitingForInvite() {
        $query = $this->createQueryBuilder('n')
            ->where('n.dateToSendInvite IS NOT NULL')
            ->getQuery();

        $orders = $query->execute();

        if (sizeof($orders) == 0) {
            return null;
        }

        return $orders;
    }
}
