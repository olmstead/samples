XML Sorting
===========
Given an XML document with the following schema, produce an ordered list of the steps as a string:

<root>
<instructions>
<step order="1">Cook spaghetti</step>
<step order="3">Add Sauce</step>
<step order="2">Drain from pot</step>
</instructions>
<dish>Pasta</dish>
</root> 


INPUT
=====
<?xml version='1.0'?> 
<root>
    <instructions>
        <step order="1">Cook spaghetti</step>
        <step order="3">Add Sauce</step>
        <step order="2">Drain from pot</step>
    </instructions>
    <dish>Pasta</dish>
</root>

OUTPUT
======
Cook spaghetti, Drain from pot, Add Sauce