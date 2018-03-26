SELECT
    actor.*,
    klub.name AS filmklub,
    klub.nummer,
    klub.region,
    land.name as land
FROM
    actor
LEFT JOIN job
ON
	actor.id = job.actor
LEFT JOIN function
ON
	job.function = function.id
inner JOIN actor AS klub
ON
    klub.id = job.target and function.display="mitglied"
LEFT JOIN function AS juror
	ON actor.id=job.actor and juror.display="juror"
LEFT JOIN land
ON
	actor.land = land.id

ORDER BY actor.name