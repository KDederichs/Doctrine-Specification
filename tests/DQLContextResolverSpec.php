<?php
declare(strict_types=1);

/**
 * This file is part of the Karusel project.
 *
 * @copyright 2010-2020 АО «Карусель» <webmaster@karusel-tv.ru>
 */

namespace tests\Happyr\DoctrineSpecification;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\DQLContextResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin DQLContextResolver
 */
final class DQLContextResolverSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this::enableDeadJoinsProtection();
        $this::enableConflictProtection();
        $this::enableAutoJoining();
    }

    public function it_resolve_not_joined_aliases(QueryBuilder $qb): void
    {
        $this::disableDeadJoinsProtection();

        $this::resolveAlias($qb, 'root')->shouldBe('root');
        $this::resolveAlias($qb, 'root.contestant')->shouldBe('contestant');
        $this::resolveAlias($qb, 'root.contestant.contest')->shouldBe('contest');
    }

    public function it_join_entity_without_conflict_protection(QueryBuilder $qb): void
    {
        $this::disableConflictProtection();

        $qb->getAllAliases()->willReturn(['contestant']);
        $qb->getDQLPart('join')->willReturn([
            'root' => [
                new Join(Join::INNER_JOIN, 'root.contestant', 'contestant'),
            ],
        ]);
        $qb->join('contestant.contest', 'contest')->willReturn($qb);

        $this::resolveAlias($qb, 'root.contestant.contest')->shouldBe('contest');
    }

    public function it_use_wrong_alias_from_another_entity(QueryBuilder $qb): void
    {
        $this::disableConflictProtection();

        $qb->getAllAliases()->willReturn(['contestant']);
        $qb->getDQLPart('join')->willReturn([
            'foo' => [
                new Join(Join::INNER_JOIN, 'foo.bar', 'contestant'),
            ],
        ]);

        $this::resolveAlias($qb, 'root.contestant')->shouldBe('contestant');
    }

    public function it_resolve_exists_alias(QueryBuilder $qb): void
    {
        $qb->getAllAliases()->willReturn(['contestant']);
        $qb->getDQLPart('join')->willReturn([
            'root' => [
                new Join(Join::INNER_JOIN, 'root.contestant', 'contestant'),
            ],
        ]);

        $this::resolveAlias($qb, 'root.contestant')->shouldBe('contestant');
    }

    public function it_resolve_wrong_alias_without_joining(QueryBuilder $qb): void
    {
        $this::disableConflictProtection();
        $this::disableAutoJoining();

        $qb->getAllAliases()->willReturn(['contestant']);
        $qb->getDQLPart('join')->willReturn([
            'foo' => [
                new Join(Join::INNER_JOIN, 'foo.bar', 'contestant'),
            ],
        ]);

        $this::resolveAlias($qb, 'root.contestant.contest')->shouldBe('contest');
    }

    public function it_join_entities(QueryBuilder $qb): void
    {
        $qb->getAllAliases()->willReturn([]);
        $qb->getDQLPart('join')->willReturn([]);
        $qb->join('root.contestant', 'contestant')->willReturn($qb);
        $qb->join('contestant.contest', 'contest')->willReturn($qb);

        $this::resolveAlias($qb, 'root.contestant.contest')->shouldBe('contest');
    }

    public function it_resolve_conflict(QueryBuilder $qb): void
    {
        $qb->getAllAliases()->willReturn(['contestant']);
        $qb->getDQLPart('join')->willReturn([
            'foo' => [
                new Join(Join::INNER_JOIN, 'foo.bar', 'contestant'),
            ],
        ]);
        $qb->join('root.contestant', Argument::that(function ($argument) {
            return preg_match('/^contestant[a-f0-9]+/', $argument);
        }))->willReturn($qb);
        $qb->join(Argument::that(function ($argument) {
            return preg_match('/^contestant[a-f0-9]+\.contest$/', $argument);
        }), 'contest')->willReturn($qb);

        $this::resolveAlias($qb, 'root.contestant.contest')->shouldBe('contest');
    }
}
