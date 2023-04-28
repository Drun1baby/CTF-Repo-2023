document.addEventListener('DOMContentLoaded', function() {
    (function hiddenFunction() {
      var _0x4a12 = `uuuuuvvvvvvvvvvvvuuvvvvvvvvvvvvvuuuuvvvvvvvvvvvvvvvuuuuuuuuuuuuuuuuuuuuuuuuuuvvvuuuu 
uuuvvvuuvvvvvvvuvvvvuuuuuuuuuuuuuuuuuuuuuuuuuvvvvvuuuu 

uuvvvuuuuuuuuuuuuuvvvvuuuuuuuvvvvuuuuuuuvvvvuuuuuuuuuuuuuuuuuuuuuuuuuuuuvvvvvvuuuu 


uvvvvuuuuvvvvvvvuvvvvvvvvvvvvvvuuuuuuuuvvvvuuuuuuuuvvvvvvvvvvvuuuuuuvvvvvvvuuuu 



uvvvvuuuvvvvuvvvvuuuuuuuuuuvvvvuuuuuuuvuuuuuvvvuuvvvvuuuu 




uvvvvuuuuuuuvvvvuvvvvuuuuuuuuuuuuuuuuuuuvvvvuuuuuuuuuuuuuuuuuuuuuuvvvvvvvvvvvvvvvvu 





uvvvvuuuuuuuvvvvuvvvvuuuuuuuuuuuuuuuuuuuvvvvuuuuuuuuuuuuuuuuuuuuuvvvvuu 






uvvvvvvvvvvvvvuuvvvvuuuuuuuuuuuuuuuuuuuvvvvuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuvvvvuuuu 







uuvuuuuvuuuuuuuuuuuuuuuuuuuuvuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuvuuuuu 








uuuuuuuuuuuuuuuuuuuuuuuuuLFKMQNHLKNHOKOHWuuuuuuuuuuuuuuuuuuuuu`;

    function _0x3aef(_0x123456) {
        var _0xabcdef = '';
        for (var _0x10 = 0x0; _0x10 < _0x123456.length; _0x10++) {
            _0xabcdef += String.fromCharCode(_0x123456.charCodeAt(_0x10) ^ 0x2a);
        }
        return _0xabcdef;
    }

    function decrypt() {
        var encodedSecret = _0x4a12;
        return _0x3aef(encodedSecret);
    }

    var currentIndex = 0;
    var decryptedSecret = decrypt();
    var outputElement = document.getElementById('output');

    function clickDecryptBtn() {
      if (currentIndex < decryptedSecret.length) {
        outputElement.textContent += decryptedSecret[currentIndex];
        currentIndex++;
      }
    }

    var GAME = document.getElementById("clickBtn");
    var LAB = document.getElementById("rapidClickBtn");

    GAME.addEventListener("click", clickDecryptBtn);

    LAB.addEventListener("click", function() {
      for (var i = 0; i < 10; i++) {
        clickDecryptBtn();
      }
    });
    
    const clickSound = document.getElementById("click-sound");
    GAME.addEventListener("click", () => {
      clickSound.currentTime = 0;
      clickSound.play();
    });
    LAB.addEventListener("click", () => {
      clickSound.currentTime = 0;
      clickSound.play();
    });

    let clickCounter = 0;
    const maxClickCount = 100000;

    LAB.addEventListener("click", function() {
      clickCounter++;
      if (clickCounter >= maxClickCount) {
        alert("您的手速过快，稍事休息，再接再厉~");
        LAB.classList.add("animated", "bounce");
        setTimeout(() => {
          LAB.classList.remove("animated", "bounce");
          const randomDelay = 3000;
          LAB.setAttribute("disabled", true);
          setTimeout(() => {
            LAB.removeAttribute("disabled");
          }, randomDelay);
          clickCounter = 0;
        }, 1000);
        return;
      }

    });


  })();
});