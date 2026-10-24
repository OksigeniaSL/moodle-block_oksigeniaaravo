<p>A block that shows a hosting client's management how their support time is spent, inside their own Moodle and read only: the hours bank (purchased, used and remaining), the included interventions of the month and whether they have been used, and a log of every piece of work with the date, what was done, the time and how it counts.</p>

<h3>How it works</h3>
<p>The data is not kept in Moodle. A central registry holds it and does the sums. The block reads a snapshot over HTTPS, keeps a local copy so it still works when the central does not answer, and never writes: recording work is the central's job.</p>

<h3>Who sees it</h3>
<p>Whoever has the <code>block/oksigeniaaravo:view</code> capability, given by default to the manager archetype, so a management role sees it and students do not. The register page adds a per-month summary and a CSV and Excel download.</p>

<h3>Requirements</h3>
<ul>
<li>Moodle 4.1 to 5.3.</li>
<li>PHP 7.4 or later.</li>
<li>Outbound HTTPS to the central registry.</li>
</ul>

<h3>Privacy</h3>
<p>The block stores no personal data about the site's users. It keeps a local copy of the support data held in the external central registry and shows it.</p>
