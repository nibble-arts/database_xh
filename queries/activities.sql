SELECT DISTINCT
	a.id as actor_id,
    activity.id as role_id,
    k.name as klub,
    r.display as funktion
FROM
    activity
LEFT JOIN role  as r ON activity.role = r.id
LEFT JOIN actor as k ON activity.target = k.id
LEFT JOIN actor as a ON a.id = {dbid}

WHERE
    activity.actor = {dbid}