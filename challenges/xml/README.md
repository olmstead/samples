XML Sorting
===========
Given an XML document with the following schema, produce an ordered list of the steps as a string:
<code>
<root>
<instructions>
<step order="1">Cook spaghetti</step>
<step order="3">Add Sauce</step>
<step order="2">Drain from pot</step>
</instructions>
<dish>Pasta</dish>
</root> 

</code>
INPUT
=====
<pre><code>
<?xml version='1.0'?> 
<root>
    <instructions>
        <step order="1">Cook spaghetti</step>
        <step order="3">Add Sauce</step>
        <step order="2">Drain from pot</step>
    </instructions>
    <dish>Pasta</dish>
</root>
</code></pre>
OUTPUT
======
Cook spaghetti, Drain from pot, Add Sauce
