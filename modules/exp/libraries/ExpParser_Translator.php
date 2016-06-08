<?php

/**
 * Handles translation from ExpParser_SearchFilter results into a list of LS
 * Filter queries
 */
class ExpParser_Translator {

	/**
	 * Creates a new instance of the translator
	 */
	public function __construct (array $search_columns) {
		$this->search_columns = $search_columns;
	}

	/**
	 * Translates the results of an ExpParser_SearchFilter->parse into a list
	 * of LS Filter queries.
	 *
	 * @param $filter array The ExpParser_SearchFilter result tree
	 * @return array Of LS filters
	 */
	public function translate(array $filter) {

		$query = array();

		/* Map default tables to queries */
		foreach($filter['filters'] as $table => $q ) {
			$query[$table] = array($this->andOrToQuery($q, $this->search_columns[$table]));
		}

		if (isset($filter['global'])) {
			/* Do not remove any tables, sub-filters below should only be used
			 * on explicit searches */
		} elseif (isset( $filter['filters']['_si'])) {
			/* Map status information table to hosts and services */
			$query['hosts'] = array_merge(
				isset($query['hosts']) ? $query['hosts'] : array(), $query['_si']
			);
			$query['services'] = array_merge(
				isset($query['services']) ? $query['services'] : array(), $query['_si']
			);
			unset( $query['_si'] );
		} elseif (isset($filter['filters']['comments'])) {
			/* Map subtables for comments (hosts and servies) */
			if (isset( $filter['filters']['services'] ) ) {
				$query['comments'][] = $this->andOrToQuery(
					$filter['filters']['services'],
					array_map(function ($col) {
						return 'service.'.$col;
					}, $this->search_columns['services'])
				);
			}
			if (isset($filter['filters']['hosts'])) {
				$query['comments'][] = $this->andOrToQuery(
					$filter['filters']['hosts'],
					array_map(function ($col) {
						return 'host.'.$col;
					}, $this->search_columns['hosts'])
				);
			}
			/* Don't search in hosts or servies if searching in comments */
			unset($query['hosts']);
			unset($query['services']);
		} elseif (isset($filter['filters']['services'])) {
			if (isset($filter['filters']['hosts'])) {
				$query['services'][] = $this->andOrToQuery(
					$filter['filters']['hosts'],
					array_map(function ($col) {
						return 'host.'.$col;
					}, $this->search_columns['hosts'])
				);
			}
			/* Don't search in hosts if searching for services, just filter on hosts... */
			unset($query['hosts']);
		}

		$result = array();
		foreach ($query as $table => $filters) {
			$result[$table] = '['.$table.'] '.implode(' and ',$filters);
		}

		if (isset($filter['limit'])) {
			$result['limit'] = intval($filter['limit']);
		}

		return $result;

	}

	/**
	 * Use to transform ExpParser query parts into LSFilter and/or query parts
	 *
	 * @param $matches array
	 * @param $columns array
	 * @return string
	 */
	private function andOrToQuery( $matches, $columns ) {
		$result = array();
        foreach( $matches as $and ) {
            $orresult = array();
            foreach( $and as $or ) {
                $or = trim($or);
                $or = str_replace('%','.*',$or);
                $or = addslashes($or);
                foreach( $columns as $col ) {
                    $orresult[] = "$col ~~ \"$or\"";
                }
            }
            $result[] = '(' . implode(' or ', $orresult) . ')';
        }

        return implode(' and ',$result);
    }

}
