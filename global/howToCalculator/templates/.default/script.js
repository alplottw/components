$(document).ready(function($) {
    let navigationCalculator = new NavigationCalc();
    let calculator = new Calculator();

    // selectize
    $('.scom-select-search select, .s-calc-page .sptw-select select').selectize({
        sortField: 'text'
    });

    $('body').find('.scp-content').on('click', '.sptw-prev', function(evt) {
        evt.preventDefault();
        navigationCalculator.prev();
        navigationCalculator.hideResult();
    });
    $('body').find('.scp-content').on('click', '.sptw-next:not(.calcNew-js)', function(evt) {
        evt.preventDefault();
        try{
            navigationCalculator.clearError();
            switch (navigationCalculator.activeStep) {
                case 2: calculator.checkStep2(); break;
                case 3: calculator.checkStep3(); break;
                case 4: calculator.checkStep4(); break;
                case 5: calculator.checkStep5(); break;
            }
            navigationCalculator.next();
        }catch(e){
            navigationCalculator.showError(e.message);
        }
    });

    $('body').find('.scp-content').on('input', '.inputParameter-js', function(evt) {
        navigationCalculator.clearError();
        calculator.setParameter(this.attributes.name.value, this.value);
        if(navigationCalculator.activeStep === 6){
            let count = calculator.calculate();
            if(count === false){
                navigationCalculator.hideResult();
            }else{
                navigationCalculator.showResult(count, calculator.radiatorId);
            }
        }
    });

    let eventSelect = function(evt){
        navigationCalculator.clearError();
        let obj = $(this).parents('.custom-select').find('select.selectParameter-js');
        if(obj.attr('name') === 'city'){
            navigationCalculator.changeCity(obj.val());
        }else if(obj.attr('name') === 'type_radiator'){
            navigationCalculator.initSelectRadiators(obj.val());
            calculator.setParameter('radiator', '0');
            console.log(calculator);
        }else if(obj.attr('name') === 'window_count'){
            navigationCalculator.showWindow(obj.val());
        }
        calculator.setParameter(obj.attr('name'), obj.val());
        if(navigationCalculator.activeStep === 6){
            let count = calculator.calculate();
            if(count === false){
                navigationCalculator.hideResult();
            }else{
                navigationCalculator.showResult(count, calculator.radiatorId);
            }
        }
    };
    $('body').find('.selectParameter-js').parent().on('click', '.select-items div', eventSelect);
    $('body').find('select.selectParameter-js').on('change', eventSelect);
    $('body').find('[data-select_city]').on('click', function(evt) {
        evt.preventDefault();
        let obj = $(this);
        let cityId = obj.attr('data-value');
        // let cityName = obj.text();
        // $('select[name=city]').find('option').attr('value', cityId).text(cityName);
        // $('select[name=city]').nextAll('.selectize-control').find('.item').attr('data-value', cityId).text(cityName);
        // $('select[name=city]').nextAll('.selectize-control').find('.selectize-dropdown .option').removeClass('active');
        // $('select[name=city]').nextAll('.selectize-control').find('.selectize-dropdown .option[data-value='+cityId+']').addClass('selected').addClass('active');
        let selCity = document.querySelector('select[name=city]').selectize;
        selCity.setValue(cityId, true);
        navigationCalculator.changeCity(cityId);
        calculator.setParameter('city', cityId);
    });

    $('body').find('.s-calc-two:not(.s-calc-two--page-index)').on('click', '[data-to_calc]', function(evt) {
        evt.preventDefault();
        navigationCalculator.next();
    });

});


function NavigationCalc() {
    this.steps = {
        1: document.querySelector('.s-calc-two[data-step="1"]'),
        2: document.querySelector('.scp-content[data-step="2"]'),
        3: document.querySelector('.scp-content[data-step="3"]'),
        4: document.querySelector('.scp-content[data-step="4"]'),
        5: document.querySelector('.scp-content[data-step="5"]'),
        6: document.querySelector('.scp-content[data-step="6"]'),
    };
    this.navSteps = document.querySelectorAll('.scp-stp[data-nav_step]');
    this.lineStep = document.querySelector('.scp-line');
    this.selectRadiators = this.steps[6] !== null ? this.steps[6].querySelector('select[name="radiator"]') : null;
    this.resultBlock = this.steps[6] !== null ? this.steps[6].querySelector('[data-result_calc]') : null;
    this.count_section = this.picture = this.name = this.type = this.link = null;
    if(this.resultBlock !== null){
        this.count_section = this.resultBlock.querySelector('[data-count_section]');
        this.picture = this.resultBlock.querySelector('[data-picture]');
        this.name = this.resultBlock.querySelector('[data-name]');
        this.type = this.resultBlock.querySelector('[data-type]');
        this.link = this.resultBlock.querySelector('[data-link]');
    }
    this.messErrors = document.querySelectorAll('.s-calc-page .error-js');
    this._widthLine = 16.667; // шаг полоски
    this.activeStep = window.location.search.indexOf('step=2') === -1 ? 1 : 2; // актинвый шаг на данный момент

    if(!this.checkElement()){ throw new Error('Ошибка не все структурные элементы присутствуют на странице'); }
}
NavigationCalc.prototype.next = function() {
    if(this.steps[this.activeStep+1] === undefined){ return false; }
    console.log(this.activeStep)
    if(this.activeStep === 1){
        this.steps[this.activeStep].style.display = 'none';
    }else{
        this.steps[this.activeStep].classList.remove('active');
    }
    this.activeStep = this.activeStep+1;
    this.steps[this.activeStep].classList.add('active');
    this.navSteps.forEach(function(elem) {
        if(elem.dataset.nav_step === undefined || elem.dataset.nav_step.trim() === '' || parseInt(elem.dataset.nav_step) <= 0) {
            return false;
        }
        if(parseInt(elem.dataset.nav_step) <= this.activeStep){
            elem.classList.add('active');
        }
    }, this);
    this.toggleSteps()
    this.lineStep.style.width = (this._widthLine*this.activeStep)+'%';
}
NavigationCalc.prototype.prev = function() {
    if(this.steps[this.activeStep-1] === undefined){ return false; }
    this.steps[this.activeStep].classList.remove('active');
    this.activeStep = this.activeStep-1;
    if(this.activeStep === 1){
        this.steps[this.activeStep].removeAttribute('style');
    }else{
        this.steps[this.activeStep].classList.add('active');
    }
    this.navSteps.forEach(function(elem) {
        if(elem.dataset.nav_step === undefined || elem.dataset.nav_step.trim() === '' || parseInt(elem.dataset.nav_step) <= 0){
            return false;
        }
        if(parseInt(elem.dataset.nav_step) <= this.activeStep){
            elem.classList.add('active');
        }else{
            elem.classList.remove('active');
        }
    }, this);
    this.toggleSteps()
    this.lineStep.style.width = (this._widthLine*this.activeStep > 100 ? 100 : this._widthLine*this.activeStep)+'%';
}
NavigationCalc.prototype.toggleSteps = function () {
    console.log(this.activeStep
    )
    if (this.activeStep != 1) {
        $('.scp-steps').addClass('active')
    } else {
        $('.scp-steps').removeClass('active')
    }
}
NavigationCalc.prototype.checkElement = function() {
    if(this.navSteps.length === 0){ return false; }
    if(this.lineStep === null){ return false; }
    if(this.selectRadiators === null){ return false; }
    for (const key in this.steps) {
        const step = this.steps[key];
        if(step === null){ return false; }
    }
    if(this.resultBlock === null || this.count_section === null || this.picture === null || this.name === null || this.type === null || this.link === null){ return false; }
    if(this.messErrors.length === 0){ return false; }
    return true;
}
NavigationCalc.prototype.changeCity = function(cityId){
    cityId = parseInt(cityId);
    if(cityId === 0){ return false; }
    if(getCities(cityId) === undefined){ return false; }
    this.steps[2].querySelector('[data-temp]').textContent = getCities(cityId).tempMiddle;
    this.steps[2].querySelector('[data-day]').textContent = getCities(cityId).countDay;
}
NavigationCalc.prototype.showWindow = function(count) {
    const maxCount = 3;
    if(count === undefined || parseInt(count) < 0){ return false; }
    count = parseInt(count);
    for(let i = 1; i <= maxCount; i++){
        let windowTitle = this.steps[3].querySelector('[data-window_title="'+i+'"]');
        let windowBlock = this.steps[3].querySelector('[data-window_block="'+i+'"]');
        let windowType = this.steps[4].querySelector('[data-window_type="'+i+'"]');
        if(i <= count){
            windowTitle.removeAttribute('style');
            windowBlock.removeAttribute('style');
            windowType.removeAttribute('style');
        }else{
            windowTitle.style.display = 'none';
            windowBlock.style.display = 'none';
            windowType.style.display = 'none';
        }
    }
}
NavigationCalc.prototype.initSelectRadiators = function(type) {
    this.selectRadiators.nextElementSibling.remove();
    this.selectRadiators.nextElementSibling.remove();
    this.selectRadiators.innerHTML = '';
    if(parseInt(type) <= 0){ return false; }

    let option = document.createElement('option');
    option.value = '0'; option.innerText = 'Выберите';
    this.selectRadiators.appendChild(option);

    for (const key in getRadiators('all')) {
        const radiator = getRadiators(key);
        if(parseInt(radiator.type) !== parseInt(type)){ continue; }
        let option = document.createElement('option');
        option.value = key; option.innerText = radiator.name;
        this.selectRadiators.appendChild(option);
    }
    initCustomSelect(this.selectRadiators.parentElement);
}
NavigationCalc.prototype.showResult = function(count, radiatorId) {
    this.count_section.innerText = count;
    this.picture.src = getRadiators(radiatorId).img;
    this.name.innerText = getRadiators(radiatorId).name;
    this.type.innerText = parseInt(getRadiators(radiatorId).type) === 1 ? 'Алюминиевый радиатор' : 'Биметалические радиаторы';
    this.link.href = getRadiators(radiatorId).link;
    this.resultBlock.removeAttribute('style');
    console.log(getRadiators(radiatorId))
}
NavigationCalc.prototype.hideResult = function() {
    this.resultBlock.style.display = 'none';
}
NavigationCalc.prototype.clearError = function() {
    this.messErrors.forEach(function(elem) { elem.innerText = ''; });
}
NavigationCalc.prototype.showError = function(message) {
    this.messErrors.forEach(function(elem) { elem.innerText = message; });
}

function Calculator() {
    this._cityId = 0;
    this._height = this._width = this._length = this._doorWidth = this._doorHeight = this._windowCount = null;
    this._paramWindows = {};

    this._typeDoor = this._typeFloor = this._typeCeiling = 'inside';
    this._typeWall = { 1: {type: 'inside'}, 2: {type: 'inside'}, 3: {type: 'inside'}, 4: {type: 'inside'} };
    this._materialWallId = this._materialWindowId = 0;

    this._temperatureEnter = 70;
    this._temperatureExit = 50;
    this.radiatorId = 0;
}
Calculator.prototype.checkStep2 = function() {
    if(parseInt(this._cityId) <= 0){ throw new Error('Выберите город'); }
    if(getCities(parseInt(this._cityId)) === undefined){ throw new Error('Выбранного города нет в списке'); }
    return true;
}
Calculator.prototype.checkStep3 = function() {
    if( !this._isNumber(this._height) || this._height <= 0 ){         throw new Error('Ошибка, высота должна быть больше 0') }
    if( !this._isNumber(this._width) || this._width <= 0 ){           throw new Error('Ошибка, ширина должна быть больше 0') }
    if( !this._isNumber(this._length) || this._length <= 0 ){         throw new Error('Ошибка, длина должна быть больше 0') }
    if( !this._isNumber(this._doorWidth) || this._doorWidth <= 0 ){   throw new Error('Ошибка, ширина двери должна быть больше 0') }
    if( !this._isNumber(this._doorHeight) || this._doorHeight <= 0 ){ throw new Error('Ошибка, высота двери должна быть больше 0') }
    if( !this._isNumber(this._windowCount) || this._windowCount < 1 || this._windowCount > 3 ){ throw new Error('Ошибка, окон должно быть от 1 до 3'); }

    for (let i = 1; i <= this._windowCount; i++) {
        const window = this._paramWindows[i];
        if(window === undefined){ throw new Error('Ошибка, параметра для окна '+i+' не указаны'); }
        if(!this._isNumber(window.width) || window.width <= 0){ throw new Error('Ошибка, ширина для окна '+i+' не указана'); }
        if(!this._isNumber(window.height) || window.height <= 0){ throw new Error('Ошибка, высота для окна '+i+' не указана'); }
    }
    return true;
}
Calculator.prototype.checkStep4 = function() {
    for (let i = 1; i <= 4; i++) {
        const wall = this._typeWall[i];
        if(wall === undefined){ throw new Error('Ошибка, не указан тип для '+i+' стены'); }
        if(!this._isSide(wall.type)){ throw new Error('Ошибка, указаный тип для стены '+i+' не существует'); }
    }

    if( !this._isSide(this._typeDoor) ){ throw new Error('Ошибка, указаный тип для двери не существует'); }

    for (let i = 1; i <= this._windowCount; i++) {
        const window = this._paramWindows[i];
        if(window === undefined){ throw new Error('Ошибка, не указан тип для '+i+' окна'); }
        if(!this._isSide(window.type)){ throw new Error('Ошибка, указаный тип для окна '+i+' не существует'); }
    }

    if( !this._isSide(this._typeFloor) ){ throw new Error('Ошибка, указаный тип для пола не существует'); }
    if( !this._isSide(this._typeCeiling) ){ throw new Error('Ошибка, указаный тип для потолка не существует'); }

    return true;
}
Calculator.prototype.checkStep5 = function() {
    if(parseInt(this._materialWallId) <= 0){ throw new Error('Выберите тип наружной стена'); }
    if(getTypeWalls(parseInt(this._materialWallId)) === undefined){ throw new Error('Выбранный тип наружной стена не существует'); }

    if(parseInt(this._materialWindowId) <= 0){ throw new Error('Выберите тип окна'); }
    if(getTypeWindows(parseInt(this._materialWindowId)) === undefined){ throw new Error('Выбранный тип окна не существует'); }

    return true;
}
Calculator.prototype.checkStep6 = function() {
    if( !this._isNumber(this._temperatureEnter) || this._temperatureEnter <= 0 ){ return false; }
    if( !this._isNumber(this._temperatureExit) || this._temperatureExit <= 0 ){   return false; }

    if(parseInt(this.radiatorId) <= 0){ return false; }
    if(getRadiators(parseInt(this.radiatorId)) === undefined){ return false; }

    return true;
}
Calculator.prototype.setParameter = function(name, value){
    switch (name) {
        case 'city':              this._cityId = this._toNumber(value);              break;
        case 'height':            this._height = this._toNumber(value);              break;
        case 'width':             this._width = this._toNumber(value);               break;
        case 'length':            this._length = this._toNumber(value);              break;
        case 'door_width':        this._doorWidth = this._toNumber(value);           break;
        case 'door_height':       this._doorHeight = this._toNumber(value);          break;
        case 'window_count':      this._windowCount = this._toNumber(value);         break;
        case 'type_door':         this._typeDoor = this._toSide(value);              break;
        case 'type_floor':        this._typeFloor = this._toSide(value);             break;
        case 'type_ceiling':      this._typeCeiling = this._toSide(value);           break;
        case 'material_wall':     this._materialWallId = this._toNumber(value);      break;
        case 'material_window':   this._materialWindowId = this._toNumber(value);    break;
        case 'temperature_enter': this._temperatureEnter = this._toNumber(value);    break;
        case 'temperature_exit':  this._temperatureExit = this._toNumber(value);     break;
        case 'radiator':          this.radiatorId = this._toNumber(value);            break;
        default:
            if(name.indexOf('window_width') !== -1 || name.indexOf('window_height') !== -1){
                let paramNum = name.split('_');
                if(paramNum.length !== 3){ return false; }
                if(this._paramWindows[paramNum[2]] === undefined){ this._paramWindows[paramNum[2]] = {}; }
                this._paramWindows[paramNum[2]][paramNum[1]] = this._toNumber(value);
                if(this._paramWindows[paramNum[2]].type === undefined){ this._paramWindows[paramNum[2]].type = 'inside'; }
            }
            if(name.indexOf('window_type') !== -1){
                let paramNum = name.split('_');
                if(paramNum.length !== 3){ return false; }
                if(this._paramWindows[paramNum[2]] === undefined){ this._paramWindows[paramNum[2]] = {}; }
                this._paramWindows[paramNum[2]][paramNum[1]] = this._toSide(value);
            }
            if(name.indexOf('wall_type') !== -1){
                let paramNum = name.split('_');
                if(paramNum.length !== 3){ return false; }
                if(this._typeWall[paramNum[2]] === undefined){ this._typeWall[paramNum[2]] = {}; }
                this._typeWall[paramNum[2]][paramNum[1]] = this._toSide(value);
            }
    }
}
Calculator.prototype._toNumber = function(val) {
    if(/[^0-9,.\s]/.test(val)){ return null; }
    return parseFloat(val.replace(/[,]/gm, '.').replace(/[\s]/gm, ''));
}
Calculator.prototype._isNumber = function(val) {
    return !/[^0-9.]/.test(val);
}
Calculator.prototype._isSide = function(val) {
    val = val.toLowerCase().trim();
    return val === 'inside' || val === 'outside';
}
Calculator.prototype._toSide = function(val) {
    val = val.toLowerCase().trim();
    return val !== 'inside' && val !== 'outside' ? null : val;
}
Calculator.prototype._koeffSide = function(side) {
    return side === 'outside' ? 1 : 0;
}
Calculator.prototype._isAllowCalc = function() {
    return this.checkStep2() && this.checkStep3() && this.checkStep4() && this.checkStep5() && this.checkStep6();
}

Calculator.prototype.calculate = function() {
    if(!this._isAllowCalc()){ return false; };

    let cityTemperature = getCities(this._cityId).value;
    let koefTypeWall = getTypeWalls(this._materialWallId).value;
    let koefTypeWindow = getTypeWindows(this._materialWindowId).value;

    let totalTeplo = 0;
    let teploWalls = {};
    let teploWindows = {};
    console.log('_doorWidth: ', this._doorWidth);
    console.log('_doorHeight: ', this._doorHeight);
    console.log('_typeDoor: ', this._typeDoor);
    console.log('_koeffSide: ', this._koeffSide(this._typeDoor));
    let teploDoor = this._doorWidth*this._doorHeight*this._koeffSide(this._typeDoor);
    teploDoor = teploDoor*(koefTypeWall-koefTypeWindow)*(22-cityTemperature)*1.15;
    let teploFloor = this._width*this._length*this._koeffSide(this._typeFloor);
    teploFloor = teploFloor*0.87*(22-cityTemperature)*0.6;
    let teploCeiling = this._width*this._length*this._koeffSide(this._typeCeiling);
    teploCeiling = teploCeiling*0.59*(22-cityTemperature);

    totalTeplo = teploDoor + teploFloor + teploCeiling;
    console.log('totalDoor: ', teploDoor)
    console.log('teploFloor): ', teploFloor)
    console.log('teploCeiling: ', teploCeiling)
    for (const key in this._typeWall) {
        const wall = this._typeWall[key];
        let width = 0;
        switch (key) {
            case '1': width = this._length; break;
            case '2': width = this._width; break;
            case '3': width = this._length; break;
            case '4': width = this._width; break;
        }
        teploWalls[key] = width*this._height*this._koeffSide(wall.type);
        if(teploWalls[key] > 0){
            teploWalls[key] = teploWalls[key]*koefTypeWall*(22-cityTemperature)*1.15;
        }
        totalTeplo += teploWalls[key];
    }
    for (const key in this._paramWindows) {
        const window = this._paramWindows[key];
        teploWindows[key] = window.width*window.height*this._koeffSide(window.type);
        if(teploWindows[key] > 0){
            teploWindows[key] = teploWindows[key]*(koefTypeWall-koefTypeWindow)*(22-cityTemperature)*1.15;
        }
        totalTeplo += teploWindows[key];
    }


    let teploSectionRadiator = getRadiators(this.radiatorId).value;
    let deltaT = ((this._temperatureEnter+this._temperatureExit)/2)-20;
    let countNeedSection = Math.round(totalTeplo/(Math.pow(deltaT/70, 1.3)*teploSectionRadiator));
    console.log('totalTeplo: ', totalTeplo)
    console.log('deltaT: ', deltaT)
    console.log('teploSectionRadiator: ', teploSectionRadiator)
    $('.calcNew-js').removeAttr('style');
    return isNaN(countNeedSection) ? '-' : countNeedSection;

}