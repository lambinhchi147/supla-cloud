<?php
namespace SuplaBundle\Model\Schedule;

use Assert\Assertion;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Entity\User;

class ScheduleListQuery {
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(Registry $doctrine) {
        $this->entityManager = $doctrine->getManager();
    }

    public function getUserSchedules(User $user, array $sort) {
        $queryBuilder = $this->getQuery();
        $this->applySort($queryBuilder, $sort);
        $query = $queryBuilder->where('s.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
        $schedules = $query->getResult();
        $this->fetchLatestExecutions($schedules);
        return $schedules;
    }

    private function getQuery():QueryBuilder {
        return $this->entityManager->createQueryBuilder()
            ->select('s schedule')
            ->addSelect(['ch.caption channel_caption', 'ch.type channel_type', 'ch.function channel_function'])
            ->addSelect('dev.name device_name')
            ->addSelect('loc.caption location_caption')
            ->from(Schedule::class, 's')
            ->join('s.channel', 'ch')
            ->join('ch.iodevice', 'dev')
            ->join('dev.location', 'loc');
    }

    private function applySort(QueryBuilder $queryBuilder, array $sort) {
        if (count($sort) != 2) {
            $sort = ['s.caption', 'asc'];
        }
        Assertion::inArray($sort[0], ['s.caption', 's.dateStart', 'channel_caption', 'device_name', 'location_caption']);
        Assertion::inArray(strtolower($sort[1]), ['asc', 'desc']);
        $queryBuilder->orderBy($sort[0], $sort[1]);
    }

    private function fetchLatestExecutions(array &$schedules) {
        $scheduleIds = implode(',', array_map(function ($schedule) { return $schedule['schedule']->getId(); }, $schedules));
        /** @var EntityManagerInterface $entityManager */
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(ScheduledExecution::class, 'e');

        $latestActionsQuery = <<<QUERY
            SELECT {$rsm->generateSelectClause()}
            FROM supla_scheduled_executions e
            INNER JOIN (
               SELECT id, MAX(result_timestamp)
                FROM supla_scheduled_executions
                WHERE schedule_id IN($scheduleIds)
                AND result_timestamp IS NOT NULL
                GROUP BY schedule_id
            ) AS t
            ON t.id = e.id;
QUERY;

        $query = $this->entityManager->createNativeQuery($latestActionsQuery, $rsm);
        /** @var ScheduledExecution[] $latestExecutions */
        $latestExecutions = $query->getResult();
        $latestExecutionsMap = [];
        foreach ($latestExecutions as $execution) {
            $latestExecutionsMap[$execution->getSchedule()->getId()] = $execution;
        }
        foreach ($schedules as &$schedule) {
            $schedule['latestExecution'] = $latestExecutionsMap[$schedule['schedule']->getId()] ?? null;
        }
    }
}