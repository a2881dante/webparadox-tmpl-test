<template>
    <div class="cart-content">
        <spinner :isSpinning="isSpinning"></spinner>
        <div v-if="models.length > 0">
            <div class="items-table">
                <table>
                    <tr v-for="model in models">
                        <td>{{model.product.name}}</td>
                        <td>
                            <ul class="items-table__property">
                                <li v-for="value in model.values">
                                    <strong>{{value.property.name}}:</strong> {{value.name}}
                                </li>
                            </ul>
                        </td>
                        <td>{{model.product.price}} грн</td>
                        <td>
                            <div class="items-table__quantity form-group">
                                <button
                                        class="form__input btn-default items-table__quantity__el"
                                        @click="subCount(model)">
                                    <i class="fas fa-minus"></i>
                                </button>

                                <input
                                        type="text"
                                        :value="model.count"
                                        class="form__input items-table__quantity__el"
                                        readonly/>

                                <button
                                        class="form__input btn-default items-table__quantity__el"
                                        @click="addCount(model)">
                                    <i class="fas fa-plus"></i>
                                </button>

                                <button
                                        class="btn-default form__input items-table__quantity__el"
                                        @click.prevent="delModel(model)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="cart-content__sum-price">
                <p>{{sum}} UAH</p>
            </div>
            <a class="btn-default"
               :href="actionCreate">Make order</a>
        </div>
        <div class="cart-content__empty" v-else>
            <p>Empty cart</p>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import { formBaseMixin } from '../../mixins/formBase';
    import { spinnerMixin } from '../../controls/Spinner';

    export default {
        name: "CartListComponent",
        mixins: [ formBaseMixin, spinnerMixin],
        props: [
            'actionCreate'
        ],
        data: function() {
            return {
                models: [],
                sum: 0,
            }
        },
        mounted() {
            this.getModels();
        },
        methods: {
            getModels() {
                this.startSpin();
                axios
                    .get(route('shop.cart.get'))
                    .then(response => {
                        this.models = response.data;
                        this.calcSum();
                    })
                    .catch(this.handleError)
                    .finally(this.stopSpin);
            },

            addCount(model) {
                this.startSpin();
                axios
                    .post(route('shop.cart.add'), {
                        modelId: model.id,
                        count: model.count+1,
                    })
                    .then(() => {
                        model.count++;
                        this.calcSum();
                    })
                    .catch(this.handleError)
                    .finally(this.stopSpin);
            },

            subCount(model) {
                this.startSpin();
                axios
                    .post(route('shop.cart.add'), {
                        modelId: model.id,
                        count: model.count-1,
                    })
                    .then(() => {
                        model.count--;
                        this.calcSum();
                    })
                    .catch(this.handleError)
                    .finally(this.stopSpin);
            },

            delModel(model) {
                this.startSpin();
                axios
                    .delete(route('shop.cart.del'), {
                        params:{
                            modelId: model.id
                        },
                    })
                    .then(() => {
                        this.models.splice(this.models.indexOf(model), 1);
                        this.calcSum();
                    })
                    .catch(this.handleError)
                    .finally(this.stopSpin);
            },

            calcSum() {
                var sum = 0;
                for(var i = 0; i < this.models.length; i++) {
                    sum += parseFloat(this.models[i].product.price)
                        * parseInt(this.models[i].count);
                }
                this.sum = sum;
            },
        }
    }
</script>