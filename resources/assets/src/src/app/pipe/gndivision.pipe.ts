import { Pipe, PipeTransform } from '@angular/core';
import { IGNdivision, ICity } from '../http/models/general.model';

@Pipe({
  name: 'gndivision'
})
export class GNdivisionPipe implements PipeTransform {

  transform(value: Array<IGNdivision>, args?: ICity): any {
    if (args === undefined || args === null || args.id  === undefined) { return ; }
    return value.filter(Obj => Obj.city_code === args.id.toString());
  }

}
