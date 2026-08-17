(function(){
'use strict';
function n(v){return (v||'').toString().trim().toLocaleLowerCase();}
function splitAuthors(s){return (s||'').split(';').map(function(x){return x.trim();}).filter(Boolean);}
function authorParts(name){
 name=(name||'').trim().replace(/\s+/g,' '); if(!name)return {given:'',surname:''};
 if(name.indexOf(',')!==-1){var c=name.split(',');return {surname:(c.shift()||'').trim(),given:c.join(',').trim()};}
 var p=name.split(' '); if(p.length===1)return {given:'',surname:p[0]};
 var particles=['da','dal','dalla','dalle','dallo','de','degli','dei','del','della','delle','dello','di','la','lo','van','von'];
 var surname=p.pop();
 if(p.length>1 && particles.indexOf((p[p.length-1]||'').toLowerCase())!==-1){surname=p.pop()+' '+surname;}
 return {surname:surname,given:p.join(' ')};
}
function initials(given){return (given||'').replace(/[.]/g,' ').split(/\s+/).filter(Boolean).map(function(x){return x.charAt(0).toUpperCase()+'.';}).join(' ');}
function apaAuthor(name){var p=authorParts(name);return p.surname+(p.given?', '+initials(p.given):'');}
function apaAuthors(names){var a=names.map(apaAuthor);if(a.length===0)return '';if(a.length===1)return a[0];if(a.length===2)return a[0]+' & '+a[1];return a.slice(0,-1).join(', ')+', & '+a[a.length-1];}
function ieeeAuthor(name){var p=authorParts(name);var i=initials(p.given);return (i?i+' ':'')+p.surname;}
function ieeeAuthors(names){var a=names.map(ieeeAuthor);if(a.length===0)return '';if(a.length===1)return a[0];if(a.length===2)return a[0]+' and '+a[1];return a.slice(0,-1).join(', ')+', and '+a[a.length-1];}
function apa(d){var a=apaAuthors(splitAuthors(d.authors));var s=(a?a+' ':'')+'('+d.year+'). '+d.title+'.';if(d.source)s+=' '+d.source;if(d.volume)s+=', '+d.volume;if(d.issue)s+='('+d.issue+')';if(d.pages)s+=', '+d.pages;s+='.';if(d.doi)s+=' https://doi.org/'+d.doi;return s.replace(/\.\./g,'.');}
function ieee(d){var a=ieeeAuthors(splitAuthors(d.authors));var s=(a?a+', ':'')+'“'+d.title+'”';if(d.source)s+=', '+d.source;if(d.volume)s+=', vol. '+d.volume;if(d.issue)s+=', no. '+d.issue;if(d.pages)s+=', pp. '+d.pages;if(d.year)s+=', '+d.year;if(d.doi)s+=', doi: '+d.doi;return s+'.';}
function bibKey(d){var a=splitAuthors(d.authors)[0]||'pang'; var last=(a.split(/\s+/).pop()||'pang').replace(/[^A-Za-z0-9]/g,'').toLowerCase(); var word=(d.title.match(/[A-Za-z0-9]+/)||['publication'])[0].toLowerCase(); return last+(d.year||'')+word;}
function bibtex(d){var type=/conference/i.test(d.type)?'inproceedings':'article'; var lines=['@'+type+'{'+bibKey(d)+',','  author = {'+splitAuthors(d.authors).join(' and ')+'},','  title = {'+d.title+'},','  year = {'+d.year+'},']; if(d.source)lines.push('  '+(type==='inproceedings'?'booktitle':'journal')+' = {'+d.source+'},'); if(d.volume)lines.push('  volume = {'+d.volume+'},'); if(d.issue)lines.push('  number = {'+d.issue+'},'); if(d.pages)lines.push('  pages = {'+d.pages.replace(/–/g,'--').replace(/-/g,'--')+'},'); if(d.doi)lines.push('  doi = {'+d.doi+'},'); if(d.publisher)lines.push('  publisher = {'+d.publisher+'},'); lines.push('}'); return lines.join('\n').replace(/,\n}/,'\n}');}
function ris(d){var type=/conference/i.test(d.type)?'CPAPER':'JOUR'; var x=['TY  - '+type]; splitAuthors(d.authors).forEach(function(a){x.push('AU  - '+a);}); x.push('TI  - '+d.title); if(d.source)x.push(type==='CPAPER'?'T2  - '+d.source:'JO  - '+d.source); if(d.year)x.push('PY  - '+d.year); if(d.volume)x.push('VL  - '+d.volume); if(d.issue)x.push('IS  - '+d.issue); if(d.pages)x.push('SP  - '+d.pages); if(d.doi)x.push('DO  - '+d.doi); if(d.publisher)x.push('PB  - '+d.publisher); x.push('ER  - '); return x.join('\r\n');}
function download(text,name,type){var b=new Blob([text],{type:type+';charset=utf-8'}); var u=URL.createObjectURL(b); var a=document.createElement('a');a.href=u;a.download=name;document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(u);},1000);}
function copy(text,status){if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(text).then(function(){status.textContent='Copied to clipboard.';});}else{status.textContent='Copy the citation from the text box below.';}}
function init(root){
 var search=root.querySelector('[data-pang-search]'), year=root.querySelector('[data-pang-year]'), author=root.querySelector('[data-pang-author]'), type=root.querySelector('[data-pang-type]'), reset=root.querySelector('[data-pang-reset]'), rows=[].slice.call(root.querySelectorAll('[data-pang-row]')), count=root.querySelector('[data-pang-visible-count]'), empty=root.querySelector('[data-pang-empty]');
 function filter(){var q=n(search&&search.value),y=n(year&&year.value),a=n(author&&author.value),t=n(type&&type.value),v=0;rows.forEach(function(r){var ok=(!q||n(r.dataset.search).indexOf(q)!==-1)&&(!y||n(r.dataset.year)===y)&&(!a||n(r.dataset.authors).indexOf(a)!==-1)&&(!t||n(r.dataset.type)===t);r.hidden=!ok;if(ok)v++;});if(count)count.textContent=v;if(empty)empty.hidden=v!==0;}
 [search].forEach(function(x){if(x)x.addEventListener('input',filter);});[year,author,type].forEach(function(x){if(x)x.addEventListener('change',filter);});if(reset)reset.addEventListener('click',function(){if(search)search.value='';if(year)year.value='';if(author)author.value='';if(type)type.value='';filter();});filter();
 var modal=root.querySelector('[data-pang-cite-modal]'), preview=root.querySelector('[data-pang-cite-preview]'), status=root.querySelector('[data-pang-cite-status]'), modalTitle=root.querySelector('[data-pang-cite-title]'), current=null;
 function close(){if(modal){modal.hidden=true;document.body.classList.remove('pang-modal-open');}}
 root.querySelectorAll('[data-pang-cite]').forEach(function(btn){btn.addEventListener('click',function(){try{current=JSON.parse(btn.getAttribute('data-cite'));}catch(e){return;}modal.hidden=false;document.body.classList.add('pang-modal-open');modalTitle.textContent=current.title;preview.value=apa(current);status.textContent='';});});
 root.querySelectorAll('[data-pang-cite-close]').forEach(function(b){b.addEventListener('click',close);});
 root.querySelectorAll('[data-cite-format]').forEach(function(b){b.addEventListener('click',function(){if(!current)return;var f=b.dataset.citeFormat,text='';root.querySelectorAll('[data-cite-format]').forEach(function(x){x.classList.remove('is-active');});b.classList.add('is-active');if(f==='apa')text=apa(current);if(f==='ieee')text=ieee(current);if(f==='bibtex')text=bibtex(current);if(f==='ris')text=ris(current);if(f==='doi')text=current.doi||'';preview.value=text;if(f==='bibtex')download(text,bibKey(current)+'.bib','application/x-bibtex');else if(f==='ris')download(text,bibKey(current)+'.ris','application/x-research-info-systems');else if(text)copy(text,status);else status.textContent='DOI not available for this record.';});});
 document.addEventListener('keydown',function(e){if(e.key==='Escape'&&modal&&!modal.hidden)close();});
}
document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('[data-pang-publications]').forEach(init);});
})();
