letter = {
    'q': 'a', 'w': 'b', 'e': 'c', 'r': 'd', 't': 'e', 'y': 'f', 'u': 'g',
    'i': 'h', 'o': 'i', 'p': 'j', 'a': 'k', 's': 'l', 'd': 'm', 'f': 'n',
    'g': 'o', 'h': 'p', 'j': 'q', 'k': 'r', 'l': 's', 'z': 't',
    'x': 'u', 'c': 'v', 'v': 'w', 'b': 'x', 'n': 'y', 'm': 'z',

    'Q': 'A', 'W': 'B', 'E': 'C', 'R': 'D', 'T': 'E', 'Y': 'F', 'U': 'G',
    'I': 'H', 'O': 'I', 'P': 'J', 'A': 'K', 'S': 'L', 'D': 'M', 'F': 'N',
    'G': 'O', 'H': 'P', 'J': 'Q', 'K': 'R', 'L': 'S', 'Z': 'T',
    'X': 'U', 'C': 'V', 'V': 'W', 'B': 'X', 'N': 'Y', 'M': 'Z',
}


def qwerty(letters):
    flag = ''
    for i in range(0, len(letters)):
        flag = flag + letter.get(letters[i])
    print(flag)

if __name__ == "__main__":
    qwerty("qwsxz")
