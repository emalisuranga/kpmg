import { IComdata } from './../http/models/recervationdata.model';
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'Search'
})
export class MainSearchPipe implements PipeTransform {

  transform(value: Array<IComdata>, args?: any): any {
    if (args === undefined) { return value; }
    return value.filter(Obj => Obj.companies.name.toLowerCase().includes(args.toLowerCase().toString()));
  }

}
