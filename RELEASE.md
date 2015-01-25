===================
Attila 1.0.0-beta2
===================

IMPORTANT CHANGE

1/ Add : initialize and onConstruct method trigger on the model
2/ Add : belongTo and hasManyToMany method on the model
3/ Add : many_to_many join in the scaffolding
4/ Add : function hasOne() and hasMany() in the entities to create join without the scaffolding
5/ Add : function getRelated() to add a call to have the results of one join where you want to put it in your entity
6/ Update : document
7/ Add the models part
8/ Add : 4 trigger on entities : initialize - onConstruct - beforeSave - afterFetch
9/ Add : begin, commit and rollback to manage transactions in the ORM

MINOR CHANGE

1/ Advance : Bug in the manytomany join in the scaffolding (it don't create the join in the entities)
2/ Bug fixed : remove static on a classic property
3/ Move file in the lib/ directory
