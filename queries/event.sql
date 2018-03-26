SELECT
	event.*,
	actor.name as name,
	actor.region as region

FROM event

left join actor
ON event.organizer = actor.id
