<?
// This is a template for a PHP scraper on morph.io (https://morph.io)
// including some code snippets below that you should find helpful

require 'scraperwiki.php';
require 'scraperwiki/simple_html_dom.php';
//
// // Read in a page
$html = scraperwiki::scrape("http://www.rba.gov.au/inflation/measures-cpi.html");
//
// // Find something on the page using css selectors
$dom = new simple_html_dom();
$dom->load($html);
$table = $dom->find("#year_ended tbody tr");

$int = - 1;
$fy  = array();

foreach ($table as $row)
{
	$class = $row->class;
	if ($class == 'tr-head')
	{
		$int   = 0;
		$year  = $row->find('th', 0)->plaintext;
		$fy    = explode('/', $year);
		$fy[1] = '20' . $fy[1];

	}
	else
	{
		if ($int === - 1)
		{
			continue;
		}

		if ($month = $row->find('th', 0))
		{
			$month = $month->plaintext;
		}

		$date = $fy[$int] . '-' . date("m", strtotime($month));

		// Consumer price index (All groups)
		$cpi = $row->find('td', 0)->plaintext;

		// Consumer price index (Excluding volatile items)
		$cpi_evi = $row->find('td', 1)->plaintext;

		//
		if ($month === 'Dec')
		{
			$int ++;
		}

		if (is_numeric($cpi))
		{
			$data[] = array('date' => $date, 'cpi' => $cpi, 'cpi_evi' => $cpi_evi);
		}

	}
}

foreach ($data as $row)
{
	scraperwiki::save_sqlite(array('date'), $row);
}

// // Write out to the sqlite database using scraperwiki library
// scraperwiki::save_sqlite(array('name'), array('name' => 'susan', 'occupation' => 'software developer'));
//
// // An arbitrary query against the database
// scraperwiki::select("* from data where 'name'='peter'")

// You don't have to do things with the ScraperWiki library.
// You can use whatever libraries you want: https://morph.io/documentation/php
// All that matters is that your final data is written to an SQLite database
// called "data.sqlite" in the current working directory which has at least a table
// called "data".
?>
