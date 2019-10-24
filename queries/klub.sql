SELECT
    actor.*,
    activity.*,
    leiter.id AS leiterid,
    leiter.titel AS leitertitel,
    leiter.name AS leitername,
    leiter.vorname AS leitervorname,
    leiter.email AS leiteremail,
    leiter.tel AS leitertel,
    land.name AS land
FROM
    actor
LEFT JOIN
    activity
ON
    actor.id = activity.target AND activity.role = 20
LEFT JOIN
    actor AS leiter
ON
    activity.actor = leiter.id
LEFT JOIN
    land
ON
    actor.land = land.id