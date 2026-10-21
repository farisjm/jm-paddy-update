let columnCount = 1;
function addWeightColumn() {
  const container=document.getElementById("weightContainer");
  if(!container) return;
  const start=(columnCount-1)*10+1, end=columnCount*10;
  const column=document.createElement("div"); column.className="weight-column";
  let html='<h3>Weights '+start+' - '+end+'</h3>';
  for(let i=start;i<=end;i++) html+='<div class="weight-row"><label>'+i+'</label><input type="number" step="0.01" min="0" class="weight-input" data-number="'+i+'" oninput="weightChanged(this)"></div>';
  html+='<div class="subtotal">Column Total: <strong><span class="column-total">0.00</span> kg</strong></div>';
  column.innerHTML=html; container.appendChild(column); columnCount++;
}
function weightChanged(input){calculateColumnTotal(input.closest(".weight-column"));autoAddNextColumn();}
function calculateColumnTotal(column){
  if(!column)return; let total=0;
  column.querySelectorAll(".weight-input").forEach(i=>total+=Math.max(0,parseFloat(i.value)||0));
  const out=column.querySelector(".column-total"); if(out)out.textContent=total.toFixed(2);
  calculateBill();
}
function autoAddNextColumn(){
  const columns=document.querySelectorAll(".weight-column"); if(!columns.length)return;
  const last=columns[columns.length-1]; const inputs=last.querySelectorAll(".weight-input");
  if(inputs.length && Array.from(inputs).every(i=>i.value!==""&&(parseFloat(i.value)||0)>0))addWeightColumn();
}
function calculateGrandTotal(){
  let total=0;document.querySelectorAll(".weight-input").forEach(i=>total+=Math.max(0,parseFloat(i.value)||0));
  const out=document.getElementById("grandTotal"), hidden=document.getElementById("total_weight");
  if(out)out.textContent=total.toFixed(2);if(hidden)hidden.value=total.toFixed(2);return total;
}
function updateRate(){
  const district=document.getElementById("district")?.value||"";
  const rate=Number((window.JMPADDY_RATES||{})[district]||0);
  const field=document.getElementById("district_rate");if(field)field.value=rate||"";
  const display=document.getElementById("displayRate");if(display)display.textContent=rate?String(rate):"—";
  calculateBill();
}
function suggestPaddyPrice(){
  const select=document.getElementById("paddy_type");if(!select)return;
  const option=select.options[select.selectedIndex];const p=Number(option?.dataset?.price||0);
  const price=document.getElementById("price");if(price&&p>0)price.value=p;
  calculateBill();
}
function calculateBill(){
  const total=calculateGrandTotal();
  const rate=Number(document.getElementById("district_rate")?.value||0);
  const price=Number(document.getElementById("price")?.value||0);
  const paid=Number(document.getElementById("paid_amount")?.value||0);
  const net=rate>0?total/rate:0, amount=Math.round((net*price+Number.EPSILON)*100)/100;
  const previous=Number(document.getElementById("previous_balance")?.value||0);
  const due=Math.max(0,Math.round((previous+amount-paid+Number.EPSILON)*100)/100);
  const set=(id,v)=>{const el=document.getElementById(id);if(el)el.textContent=Number(v).toLocaleString("en-LK",{minimumFractionDigits:2,maximumFractionDigits:2});};
  set("netWeight",net);set("totalAmount",amount);set("previousBalance",previous);set("totalPayable",previous+amount);set("paidDisplay",paid);set("balanceDue",due);
  const n=document.getElementById("net_weight"),a=document.getElementById("total_amount"),b=document.getElementById("balance_due");
  if(n)n.value=net.toFixed(4);if(a)a.value=amount.toFixed(2);if(b)b.value=due.toFixed(2);
  const weights=[];document.querySelectorAll(".weight-input").forEach(i=>{if(i.value!==""&&(Number(i.value)||0)>0)weights.push({number:Number(i.dataset.number),weight:Number(i.value)});});
  const wd=document.getElementById("weightsData");if(wd)wd.value=JSON.stringify(weights);
}
async function loadFarmerBalance(){
  const phone=document.getElementById("phone")?.value.trim()||"";
  const field=document.getElementById("previous_balance");if(!field)return;
  if(!phone){field.value="0";calculateBill();return;}
  try{
    const edit=Number(window.JMPADDY_EDIT_ID||0);
    const response=await fetch("farmer_balance.php?phone="+encodeURIComponent(phone)+"&exclude="+edit,{headers:{"Accept":"application/json"}});
    if(!response.ok)return;
    const data=await response.json();field.value=Number(data.balance||0).toFixed(2);calculateBill();
  }catch(e){console.warn("Could not refresh farmer balance",e);}
}
function resetWeights(){
  setTimeout(()=>{const c=document.getElementById("weightContainer");if(c)c.innerHTML="";columnCount=1;addWeightColumn();["grandTotal","netWeight","totalAmount","previousBalance","totalPayable","paidDisplay","balanceDue"].forEach(id=>{const el=document.getElementById(id);if(el)el.textContent="0.00";});calculateBill();},10);
}
window.addEventListener("load",()=>{
  addWeightColumn();
  const initial=Array.isArray(window.JMPADDY_INITIAL_WEIGHTS)?window.JMPADDY_INITIAL_WEIGHTS:[];
  initial.forEach(w=>{
    const number=Number(w.number),value=Number(w.weight);if(number<1||value<=0)return;
    while((columnCount-1)*10<number)addWeightColumn();
    const input=document.querySelector('.weight-input[data-number="'+number+'"]');if(input)input.value=value;
  });
  document.querySelectorAll(".weight-column").forEach(c=>calculateColumnTotal(c));
  calculateBill();
});