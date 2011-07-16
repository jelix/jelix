{meta main count($items)}{meta_testinc counter}
c={=count($items)}
{assign $dummy=array_pop($items)}
x={$dummy}
{if count($items) > 0}{include 'test_include_recursive'}{/if}
