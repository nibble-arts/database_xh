SELECT

actor.*,
job.*,
leiter.titel as leitertitel,
leiter.name as leitername,
leiter.vorname as leitervorname,
leiter.email as leiteremail,
leiter.tel as leitertel,
land.name as land

from actor

left join job on actor.id=job.target and job.function=20
left join actor as leiter on job.actor=leiter.id
left join land on actor.land=land.id