<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchDSL\SearchEndpoint;

use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FilteredQuery;
use ONGR\ElasticsearchDSL\Serializer\Normalizer\OrderedNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Search query dsl endpoint.
 */
class QueryEndpoint extends AbstractSearchEndpoint implements OrderedNormalizerInterface
{
    /**
     * Endpoint name
     */
    const NAME = 'query';

    /**
     * @var BoolQuery
     */
    private $bool;

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        $query = $this->getBool();

        if ($this->hasReference('filtered_query')) {
            /** @var FilteredQuery $filteredQuery */
            $filteredQuery = $this->getReference('filtered_query');

            if ($query) {
                $filteredQuery->setQuery($query);
            }

            $query = $filteredQuery;
        }

        if (!$query) {
            return null;
        }

        return [$query->getType() => $query->toArray()];
    }

    /**
     * {@inheritdoc}
     */
    public function add(BuilderInterface $builder, $key = null)
    {
        return $this->addToBool($builder, BoolQuery::MUST, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function addToBool(BuilderInterface $builder, $boolType = null, $key = null)
    {
        if (!$this->bool) {
            $this->bool = $this->getBoolInstance();
        }

        return $this->bool->add($builder, $boolType, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * @return BoolQuery
     */
    public function getBool()
    {
        return $this->bool;
    }

    /**
     * Returns new bool instance for the endpoint.
     *
     * @return BoolQuery
     */
    protected function getBoolInstance()
    {
        return new BoolQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($boolType = null)
    {
        return $this->bool->getQueries($boolType);
    }
}
