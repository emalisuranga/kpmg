export class mFormData {
    id: string = '';
    name: string = '';
    postfix: string = '';
}

export class NameDetails {
    id: string = '';
    name: string = '';
    name_si: string = '';
    name_ta: string = '';
    abbreviation_desc: string = '';
    postfix: string = '';
    clear() {
        this.id = '';
        this.name = '';
        this.name_si = '';
        this.name_ta = '';
        this.abbreviation_desc = '';
        this.postfix = '';
    }
}
