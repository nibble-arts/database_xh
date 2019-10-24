SELECT
    actor.*,
    role.display AS role,
    role.hierarchy AS hierarchy,
    land.name AS land,
    target.name as target,
    actor.region as leiterregion
FROM
    actor
LEFT JOIN
    activity
ON
    actor.id = activity.actor
LEFT JOIN
    actor AS target
ON
    target.id = activity.target
LEFT JOIN
    role
ON
    activity.role = role.id
LEFT JOIN
    land
ON
    actor.land = land.id