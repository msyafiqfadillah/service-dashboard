function currency(number) {
    return Intl.NumberFormat(navigator.languages, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
}