const selectGenero = document.getElementById("opcoes");
const campoExtra = document.getElementById("campoExtra");

campoExtra.style.display = "none";
selectGenero.addEventListener("change", () => {

if(selectGenero.value === "outro"){
    campoExtra.style.display = "block";
}else{
    campoExtra.style.display = "none";
}
});