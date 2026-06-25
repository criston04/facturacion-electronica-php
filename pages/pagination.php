<?php
function paginate($page, $tpages, $adjacents) {
	$prevlabel = "&lsaquo; Anterior";
	$nextlabel = "Siguiente &rsaquo;";
	$out = '<div class="mt-6 flex justify-end"><ul class="inline-flex items-center -space-x-px">';

	// Previous
	if ($page == 1) {
		$out .= "<li><span class='px-3 py-2 ml-0 leading-tight text-gray-400 bg-white border border-gray-300 rounded-l-lg cursor-not-allowed'>$prevlabel</span></li>";
	} else {
		$out .= "<li><a href='javascript:void(0);' onclick='load(" . ($page - 1) . ")' class='px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700'>$prevlabel</a></li>";
	}

	// First page
	if ($page > ($adjacents + 1)) {
		$out .= "<li><a href='javascript:void(0);' onclick='load(1)' class='px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700'>1</a></li>";
	}

	// Ellipsis before
	if ($page > ($adjacents + 2)) {
		$out .= "<li><span class='px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300'>...</span></li>";
	}

	// Page numbers
	$pmin = ($page > $adjacents) ? ($page - $adjacents) : 1;
	$pmax = ($page < ($tpages - $adjacents)) ? ($page + $adjacents) : $tpages;
	for ($i = $pmin; $i <= $pmax; $i++) {
		if ($i == $page) {
			$out .= "<li><span class='px-3 py-2 leading-tight text-white bg-blue-600 border border-blue-600'>$i</span></li>";
		} else {
			$out .= "<li><a href='javascript:void(0);' onclick='load($i)' class='px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700'>$i</a></li>";
		}
	}

	// Ellipsis after
	if ($page < ($tpages - $adjacents - 1)) {
		$out .= "<li><span class='px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300'>...</span></li>";
	}

	// Last page
	if ($page < ($tpages - $adjacents)) {
		$out .= "<li><a href='javascript:void(0);' onclick='load($tpages)' class='px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700'>$tpages</a></li>";
	}

	// Next
	if ($page < $tpages) {
		$out .= "<li><a href='javascript:void(0);' onclick='load(" . ($page + 1) . ")' class='px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700'>$nextlabel</a></li>";
	} else {
		$out .= "<li><span class='px-3 py-2 leading-tight text-gray-400 bg-white border border-gray-300 rounded-r-lg cursor-not-allowed'>$nextlabel</span></li>";
	}

	$out .= "</ul></div>";
	return $out;
}
?>
