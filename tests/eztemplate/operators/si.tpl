0={0|si(byte)}
0={0|si(byte,binary)}
0={0|si(byte,decimal)}
0={0|si(byte,none)}
0={0|si(byte,auto)}
0={0|si(byte,kibi)}
0={0|si(byte,kilo)}

3={3|si(byte)}
3={3|si(byte,binary)}
3={3|si(byte,decimal)}
3={3|si(byte,none)}
3={3|si(byte,auto)}
3={3|si(byte,kibi)}
3={3|si(byte,kilo)}

25={25|si(byte)}
25={25|si(byte,binary)}
25={25|si(byte,decimal)}
25={25|si(byte,none)}
25={25|si(byte,auto)}
25={25|si(byte,kibi)}
25={25|si(byte,kilo)}

1025={1025|si(byte)}
1025={1025|si(byte,binary)}

{let decimal=1025|si(byte,decimal) kilo=1025|si(byte,kilo)}
1025=
{section show=eq($decimal, $exp_decimal)}
success
{section-else}
failed
{/section}

1025={1025|si(byte,none)}
1025={1025|si(byte,auto)}
1025={1025|si(byte,kibi)}
1025=
{section show=eq($kilo, $exp_kilo)}
success
{section-else}
failed
{/section}
{/let}


1048577={1048577|si(byte)}
1048577={1048577|si(byte,binary)}
1048577={1048577|si(byte,decimal)}
1048577={1048577|si(byte,none)}
1048577={1048577|si(byte,auto)}
1048577={1048577|si(byte,mebi)}
1048577={1048577|si(byte,mega)}


{let value=1025 var_decimal=$value|si(byte,decimal) var_kilo=$value|si(byte,kilo)}
1025={$value|si(byte)}
1025={$value|si(byte,binary)}
1025=
{section show=eq($var_decimal, $exp_decimal)}
success
{section-else}
failed
{/section}

1025={$value|si(byte,none)}
1025={$value|si(byte,auto)}
1025={$value|si(byte,kibi)}
1025=
{section show=eq($var_kilo, $exp_kilo)}
success
{section-else}
failed
{/section}
{/let}