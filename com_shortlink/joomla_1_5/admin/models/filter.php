<?php
/**
 * Helper class to build parts of the SQL clause
 */
class ShortlinksFilter
{
	var $keyword;
	
	var $last_call;

	var $filter_order;
	var $filter_order_dir;
	
	var $limitstart;
	var $limit;
	

	function setOrder($filter_order, $filter_order_dir)
	{
		$this->filter_order = $filter_order;
		$this->filter_order_dir = $filter_order_dir;
	}

	function setPageLimits($limitstart, $limit)
	{
		$this->limitstart = $limitstart;
		$this->limit = $limit;
	}
	
	function getLimitstart()
	{
		return $this->limitstart;
	}

	function getLimit()
	{
		return $this->limit;
	}

	function setKeyword($keyword)
	{
		$this->keyword = $keyword;
	}
	
	function setLastCall($last_call)
	{
		$this->last_call = $last_call;
	}

	function getLastCallSelections()
	{
    	$texts['total'] = "show all";
		$texts['never'] = "never called";
		$texts['last_weeks_1'] = "called within last week";
		$texts['last_weeks_2'] = "called within last 2 weeks";
		$texts['last_weeks_3'] = "called within last 3 weeks";
		$texts['last_weeks_4'] = "called within last 4 weeks";
		$texts['last_months_1'] = "called within last month";
		$texts['last_months_2'] = "called within last 2 months";
		$texts['last_months_3'] = "called within last 3 months";
		$texts['last_months_4'] = "called within last 4 months";
		$texts['last_months_5'] = "called within last 5 months";
		$texts['last_months_6'] = "called within last 6 months";
		$texts['last_year'] = "called within last year";
		
		return $texts;
	}

	function &appendLastCallClauses($where_main)
	{
		$one_day = 60 * 60 * 24;
    	$time_now = time();

    	$wheres['total'] = $where_main;
		$wheres['never'] = $where_main.' AND last_call = \'0000-00-00 00:00:00\'';
		$wheres['last_year'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 365).'\'';
		$wheres['last_months_6'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 183).'\'';
		$wheres['last_months_5'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 152).'\'';
		$wheres['last_months_4'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 122).'\'';
		$wheres['last_months_3'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 91).'\'';
		$wheres['last_months_2'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 61).'\'';
		$wheres['last_months_1'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 31).'\'';
		$wheres['last_weeks_4'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 28).'\'';
		$wheres['last_weeks_3'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 21).'\'';
		$wheres['last_weeks_2'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 14).'\'';
		$wheres['last_weeks_1'] = $where_main.' AND last_call > \''.date("Y-m-d H:i:s", $time_now - $one_day * 7).'\'';
		
		return $wheres;
    }

	function getWhere()
	{
		$where_main = $this->getWhereMain();
		
		if ($this->last_call && $this->last_call != 'total')
		{
			if (empty($where_main))
			{
				$where_main = ' WHERE 1=1 ';
			}

			$wheres = $this->appendLastCallClauses($where_main);

			$where = $wheres[$this->last_call];
		}
		else
		{
			$where = $where_main;
		}

		return $where;
	}

	function getWhereMain()
	{
		$db =& JFactory::getDBO();
		
		$where = array();

		if ($this->keyword)
		{
			$keyword_escaped = $db->quote( '%'.$db->getEscaped( $this->keyword, true ).'%', false );

			// TODO what about the article ids in column 'link'?!
			$where_keyword  = '( LOWER(`phrase`) like '.$keyword_escaped.' OR LOWER(`description`) like '.$keyword_escaped.' OR LOWER(`link`) like '.$keyword_escaped.')';
			$where[] = $where_keyword;
		}

		if ($this->last_call_min)
		{
			$where[] = '`last_call` >= '.$db->quote( $db->getEscaped( $this->last_call_min ), false );
		}
		
		if ($this->last_call_max)
		{
			$where[] = '`last_call` <= '.$db->quote( $db->getEscaped( $this->last_call_max ), false );
		}

		$where = count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		
		return $where;
	}
	
	function getOrderBy()
	{
		$order_by = '';

		if ($this->filter_order)
		{
			// TODO what about the article ids in column 'link'?!
			$order_by = ' ORDER BY '.$this->filter_order;

			if ($this->filter_order_dir)
			{
				$order_by .= ' '.$this->filter_order_dir.' ';
			}
		}
		
		return $order_by;
	}
}
?>