SELECT
    actor.titel,
    actor.name,
    actor.vorname,
    activity.event as event,
    activity.description as vorsitz

FROM
    actor
LEFT JOIN
    activity
ON
    actor.id = activity.actor AND activity.role = 25
LEFT JOIN event
ON
    activity.event = event.id