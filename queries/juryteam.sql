SELECT
     GROUP_CONCAT( CONCAT(ersatz.vorname,' ',ersatz.name)) as ersatz,
     ersatz.titel as ersatztitel,
     GROUP_CONCAT( CONCAT(sekret.vorname,' ',sekret.name)) as sekret,
     sekret.titel as sekrettitel,
     GROUP_CONCAT( CONCAT(technical.vorname,' ',technical.name)) as technical,
     technical.titel as technicalztitel,
     event.id
FROM event

LEFT JOIN
     activity as p
ON event.id = p.event

LEFT JOIN
     actor as ersatz
ON p.actor = ersatz.id and p.role = 36

LEFT JOIN
     actor as sekret
ON p.actor = sekret.id and p.role = 35

LEFT JOIN
     actor as technical
ON p.actor = technical.id and p.role = 13