<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DatagridBundle\Pager\Doctrine;

use Doctrine\ORM\Query;
use Sonata\DatagridBundle\Pager\BasePager;

/**
 * Doctrine pager class.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
final class Pager extends BasePager
{
    /**
     * @return int
     */
    public function computeNbResult(): int
    {
        $countQuery = clone $this->getQuery();

        if (count($this->getParameters()) > 0) {
            $countQuery->setParameters($this->getParameters());
        }

        if ($countQuery->getQueryBuilder()->getDQLPart('orderBy')) {
            $countQuery->getQueryBuilder()->resetDQLPart('orderBy');
        }

        $countQuery->select(sprintf(
            'count(DISTINCT %s.%s) as cnt',
            current($countQuery->getRootAliases()),
            current($this->getCountColumn())
        ));

        return $countQuery->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($hydrationMode = Query::HYDRATE_OBJECT): ?array
    {
        return $this->getQuery()->execute(array(), $hydrationMode);
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->resetIterator();

        $this->setNbResults($this->computeNbResult());

        $this->getQuery()->setFirstResult(null);
        $this->getQuery()->setMaxResults(null);

        if (count($this->getParameters()) > 0) {
            $this->getQuery()->setParameters($this->getParameters());
        }

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults()) {
            $this->setLastPage(1);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

            $this->getQuery()->setFirstResult($offset);
            $this->getQuery()->setMaxResults($this->getMaxPerPage());
        }
    }
}
