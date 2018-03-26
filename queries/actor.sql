SELECT
    actor.*,
    function.display AS function,
    function.hierarchy AS hierarchy,
    function.vorstand AS vorstand,
	job.target AS leiterregion,
    land.name AS land
FROM
    actor
LEFT JOIN job ON actor.id = job.actor
LEFT JOIN function ON job.function = function.id
LEFT JOIN land ON actor.land = land.id