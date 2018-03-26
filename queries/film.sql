# get film, author and klub informations
SELECT
    film.titel,
    film.laufzeit,
    film.entstehung,
    aspect.display as aspect,
    aspect.width,
    aspect.height,
    author.name,
    author.vorname,
    author.ort,
    land.name AS land,
    author.email,
    klub.name as klub,
    klub.region as region
FROM
    film
LEFT JOIN actor AS author
ON
    film.author = author.id
LEFT JOIN land ON author.land = land.id
LEFT JOIN aspect ON film.aspect = aspect.id
LEFT JOIN actor AS klub
ON
    film.klub=klub.id