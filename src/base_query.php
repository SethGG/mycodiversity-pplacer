<?php

$base_query = 'SELECT *
FROM "Sample" S
JOIN "Contain" C ON S.sample_pk = C.sample_pk
JOIN "RefSequence" R ON C.refsequence_pk = R.refsequence_pk';

?>